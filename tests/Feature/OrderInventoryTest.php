<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderInventoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customers can be created and enforce unique email.
     */
    public function test_customer_creation_and_unique_email(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Thomas Anderson',
            'email' => 'thomas@example.com',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Thomas Anderson',
            'email' => 'thomas@example.com',
        ]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Customer::factory()->create(['email' => 'thomas@example.com']);
    }

    /**
     * Test product low stock scope identifies items below or at threshold.
     */
    public function test_product_low_stock_scope(): void
    {
        $lowStockProduct = Product::factory()->create([
            'name' => 'Bread',
            'code' => 'PRD-BRED-01',
            'stock_on_hand' => 4,
            'low_stock_threshold' => 10,
        ]);

        $inStockProduct = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'code' => 'PRD-COLG-01',
            'stock_on_hand' => 25,
            'low_stock_threshold' => 10,
        ]);

        $lowStockItems = Product::lowStock()->pluck('code')->toArray();

        $this->assertContains('PRD-BRED-01', $lowStockItems);
        $this->assertNotContains('PRD-COLG-01', $lowStockItems);
        $this->assertTrue($lowStockProduct->isLowStock());
        $this->assertFalse($inStockProduct->isLowStock());
    }

    /**
     * Test order calculation with multiple line items, tax, and stock movement.
     */
    public function test_order_creation_with_line_items_and_stock_deduction(): void
    {
        $customer = Customer::factory()->create();

        $colgate = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'price_per_unit' => 50.00,
            'tax_percentage' => 8.00,
            'stock_on_hand' => 25,
        ]);

        $parleG = Product::factory()->create([
            'name' => 'Parle-G Biscuit',
            'price_per_unit' => 10.00,
            'tax_percentage' => 8.00,
            'stock_on_hand' => 50,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'status' => 'completed',
            'paid_amount' => 200.00,
        ]);

        // Line 1: 2 Colgate @ 50 = 100, tax 8% = 8, total = 108
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $colgate->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'tax_percentage' => 8.00,
            'subtotal' => 100.00,
            'tax_amount' => 8.00,
            'total' => 108.00,
        ]);
        $colgate->recordStockMovement(-2, 'sale', $order->id, 'Order test sale');

        // Line 2: 5 Parle-G @ 10 = 50, tax 8% = 4, total = 54
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $parleG->id,
            'quantity' => 5,
            'unit_price' => 10.00,
            'tax_percentage' => 8.00,
            'subtotal' => 50.00,
            'tax_amount' => 4.00,
            'total' => 54.00,
        ]);
        $parleG->recordStockMovement(-5, 'sale', $order->id, 'Order test sale');

        $order->recalculateTotals();

        // Verify Order Totals
        $this->assertEquals(150.00, $order->subtotal);
        $this->assertEquals(12.00, $order->tax_amount);
        $this->assertEquals(162.00, $order->grand_total);
        $this->assertEquals(38.00, $order->change_amount); // 200 - 162 = 38

        // Verify Denominations: 38 = 1x20 + 1x10 + 1x5 + 1x2 + 1x1
        $denominations = $order->calculateChangeDenominations();
        $this->assertEquals('1x20 + 1x10 + 1x5 + 1x2 + 1x1', $denominations['summary']);

        // Verify Stock Deductions
        $this->assertEquals(23, $colgate->fresh()->stock_on_hand);
        $this->assertEquals(45, $parleG->fresh()->stock_on_hand);

        // Verify Stock Movements
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $colgate->id,
            'order_id' => $order->id,
            'type' => 'sale',
            'quantity_change' => -2,
            'stock_after' => 23,
        ]);
    }

    /**
     * Test that database seeders run cleanly and populate wireframe reference data.
     */
    public function test_seeders_populate_expected_wireframe_data(): void
    {
        $this->seed();

        // Wireframe customer exists
        $this->assertDatabaseHas('customers', [
            'email' => 'thomas@example.com',
            'name' => 'Thomas Anderson',
        ]);

        // Wireframe products exist
        $this->assertDatabaseHas('products', ['code' => 'PRD-COLG-01', 'name' => 'Colgate Toothpaste']);
        $this->assertDatabaseHas('products', ['code' => 'PRD-PARL-01', 'name' => 'Parle-G Biscuit']);
        $this->assertDatabaseHas('products', ['code' => 'PRD-BRED-01', 'name' => 'Bread']);
        $this->assertDatabaseHas('products', ['code' => 'PRD-MILK-01', 'name' => 'Milk 1L']);
        $this->assertDatabaseHas('products', ['code' => 'PRD-EGGS-01', 'name' => 'Eggs (12)']);

        // Low stock products alert matches wireframe
        $lowStockCodes = Product::lowStock()->pluck('code')->toArray();
        $this->assertContains('PRD-BRED-01', $lowStockCodes);
        $this->assertContains('PRD-MILK-01', $lowStockCodes);
        $this->assertContains('PRD-EGGS-01', $lowStockCodes);

        // Orders exist with line items
        $this->assertGreaterThan(0, Order::count());
        $this->assertGreaterThan(0, OrderItem::count());
        $this->assertGreaterThan(0, StockMovement::count());
    }
}

