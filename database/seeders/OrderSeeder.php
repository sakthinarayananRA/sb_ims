<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerThomas = Customer::where('email', 'thomas@example.com')->first();
        $colgate = Product::where('code', 'PRD-COLG-01')->first();
        $parleG = Product::where('code', 'PRD-PARL-01')->first();
        $bread = Product::where('code', 'PRD-BRED-01')->first();

        // 1. Seed the order inspired by the Wireframe
        if ($customerThomas && $colgate && $parleG) {
            DB::transaction(function () use ($customerThomas, $colgate, $parleG, $bread) {
                $order = Order::create([
                    'order_number' => 'ORD-' . date('Ymd') . '-0001',
                    'customer_id' => $customerThomas->id,
                    'status' => 'completed',
                    'subtotal' => 0.00,
                    'tax_amount' => 0.00,
                    'grand_total' => 0.00,
                    'paid_amount' => 250.00,
                    'change_amount' => 0.00,
                ]);

                $lines = [
                    ['product' => $colgate, 'qty' => 2], // 2 x 50 = 100, tax 8% = 8 -> 108
                    ['product' => $parleG, 'qty' => 5],  // 5 x 10 = 50, tax 8% = 4 -> 54
                    ['product' => $bread, 'qty' => 1],   // 1 x 40 = 40, tax 5% = 2 -> 42
                ];

                foreach ($lines as $line) {
                    /** @var Product $product */
                    $product = $line['product'];
                    $qty = $line['qty'];
                    $unitPrice = $product->price_per_unit;
                    $taxPct = $product->tax_percentage;
                    $subtotal = round($qty * $unitPrice, 2);
                    $taxAmount = round($subtotal * ($taxPct / 100), 2);
                    $total = round($subtotal + $taxAmount, 2);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'tax_percentage' => $taxPct,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total' => $total,
                    ]);

                    // Deduct stock and log movement
                    $product->recordStockMovement(
                        quantityChange: -$qty,
                        type: 'sale',
                        orderId: $order->id,
                        remarks: "Sold in order {$order->order_number}"
                    );
                }

                $order->recalculateTotals();
            });
        }

        // 2. Seed a few additional sample orders for other customers
        $otherCustomers = Customer::where('email', '!=', 'thomas@example.com')->take(3)->get();
        $availableProducts = Product::where('stock_on_hand', '>', 5)->get();

        foreach ($otherCustomers as $idx => $customer) {
            DB::transaction(function () use ($customer, $availableProducts, $idx) {
                $orderNum = sprintf('ORD-%s-%04d', date('Ymd'), $idx + 2);
                $order = Order::create([
                    'order_number' => $orderNum,
                    'customer_id' => $customer->id,
                    'status' => 'completed',
                    'subtotal' => 0.00,
                    'tax_amount' => 0.00,
                    'grand_total' => 0.00,
                ]);

                // Pick 2 random products
                $selectedProducts = $availableProducts->random(min(2, $availableProducts->count()));
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $unitPrice = $product->price_per_unit;
                    $taxPct = $product->tax_percentage;
                    $subtotal = round($qty * $unitPrice, 2);
                    $taxAmount = round($subtotal * ($taxPct / 100), 2);
                    $total = round($subtotal + $taxAmount, 2);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'tax_percentage' => $taxPct,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total' => $total,
                    ]);

                    $product->recordStockMovement(
                        quantityChange: -$qty,
                        type: 'sale',
                        orderId: $order->id,
                        remarks: "Sold in order {$order->order_number}"
                    );
                }

                $order->recalculateTotals();

                // Set paid amount slightly above grand total to simulate cash payment & change
                $grandTotal = (float) $order->grand_total;
                $roundedPayment = ceil($grandTotal / 50) * 50; // nearest 50
                if ($roundedPayment < $grandTotal) {
                    $roundedPayment += 50;
                }
                $order->paid_amount = $roundedPayment;
                $order->change_amount = round($roundedPayment - $grandTotal, 2);
                $order->save();
            });
        }
    }
}

