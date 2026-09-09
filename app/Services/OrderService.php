<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Atomically create an order with stock validation, tax calculation, and inventory deduction.
     *
     * @param array{
     *     customer_name: string,
     *     customer_email: string,
     *     customer_phone?: string|null,
     *     items: list<array{product_id: int, quantity: int}>,
     *     paid_amount?: float|numeric|null
     * } $data
     *
     * @throws InsufficientStockException
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $email = strtolower(trim($data['customer_email']));

            // 1. Find or create the customer
            $customer = Customer::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                ]
            );

            // Sync name or phone if updated
            $updates = [];
            if (!empty($data['customer_name']) && $customer->name !== $data['customer_name']) {
                $updates['name'] = $data['customer_name'];
            }
            if (isset($data['customer_phone']) && $customer->phone !== $data['customer_phone']) {
                $updates['phone'] = $data['customer_phone'];
            }
            if (!empty($updates)) {
                $customer->update($updates);
            }

            // 2. Lock requested products with pessimistic lock to prevent concurrency overselling
            $itemsCollection = collect($data['items']);
            $productIds = $itemsCollection->pluck('product_id')->unique()->all();

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 3. Validate stock on hand for each line item
            $deficits = [];
            foreach ($itemsCollection as $item) {
                /** @var Product|null $product */
                $product = $products->get($item['product_id']);
                $requestedQty = (int) $item['quantity'];

                if (!$product || $product->stock_on_hand < $requestedQty) {
                    $available = $product ? (int) $product->stock_on_hand : 0;
                    $deficits[] = [
                        'product_id' => (int) $item['product_id'],
                        'product_name' => $product?->name ?? 'Unknown Product',
                        'requested_quantity' => $requestedQty,
                        'stock_on_hand' => $available,
                        'shortage' => $requestedQty - $available,
                    ];
                }
            }

            if (!empty($deficits)) {
                throw new InsufficientStockException($deficits);
            }

            // 4. Generate unique order number
            $prefix = config('inventory.order_prefix', 'ORD');
            $date = date('Ymd');
            $count = Order::whereDate('created_at', today())->count() + 1;
            $orderNumber = sprintf('%s-%s-%04d', $prefix, $date, $count);
            while (Order::where('order_number', $orderNumber)->exists()) {
                $count++;
                $orderNumber = sprintf('%s-%s-%04d', $prefix, $date, $count);
            }

            // 5. Create Order header
            $paidAmount = isset($data['paid_amount']) ? (float) $data['paid_amount'] : null;
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'status' => 'completed',
                'subtotal' => 0.00,
                'tax_amount' => 0.00,
                'grand_total' => 0.00,
                'paid_amount' => $paidAmount,
                'change_amount' => 0.00,
            ]);

            // 6. Create line items, compute taxes, and deduct inventory
            foreach ($itemsCollection as $item) {
                /** @var Product $product */
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price_per_unit;
                $taxPercentage = (float) $product->tax_percentage;

                $subtotal = round($quantity * $unitPrice, 2);
                $taxAmount = round($subtotal * ($taxPercentage / 100), 2);
                $total = round($subtotal + $taxAmount, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxPercentage,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                ]);

                // Deduct stock and record audit trail movement
                $product->recordStockMovement(
                    quantityChange: -$quantity,
                    type: 'sale',
                    orderId: $order->id,
                    remarks: "Sold in order {$order->order_number}"
                );
            }

            // 7. Calculate and save order totals & change
            $order->recalculateTotals();

            return $order->fresh(['customer', 'items.product', 'stockMovements']);
        });
    }
}
