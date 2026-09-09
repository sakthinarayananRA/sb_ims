<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful order creation via POST /api/v1/orders.
     */
    public function test_can_create_order_successfully(): void
    {
        $product1 = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'code' => 'PRD-COLG-01',
            'price_per_unit' => 50.00,
            'tax_percentage' => 8.00,
            'stock_on_hand' => 20,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Parle-G Biscuit',
            'code' => 'PRD-PARL-01',
            'price_per_unit' => 10.00,
            'tax_percentage' => 8.00,
            'stock_on_hand' => 30,
        ]);

        $payload = [
            'customer_name' => 'Thomas Anderson',
            'customer_email' => 'thomas@example.com',
            'customer_phone' => '+91 98765 43210',
            'paid_amount' => 200.00,
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 5],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Order created successfully.',
            ])
            ->assertJsonPath('data.customer.name', 'Thomas Anderson')
            ->assertJsonPath('data.customer.email', 'thomas@example.com');

        $this->assertEquals(150.00, $response->json('data.subtotal'));
        $this->assertEquals(12.00, $response->json('data.tax_amount'));
        $this->assertEquals(162.00, $response->json('data.grand_total'));
        $this->assertEquals(200.00, $response->json('data.paid_amount'));
        $this->assertEquals(38.00, $response->json('data.change_amount'));

        // Verify database stock deduction
        $this->assertEquals(18, $product1->fresh()->stock_on_hand);
        $this->assertEquals(25, $product2->fresh()->stock_on_hand);

        // Verify audit movement records
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product1->id,
            'type' => 'sale',
            'quantity_change' => -2,
            'stock_after' => 18,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product2->id,
            'type' => 'sale',
            'quantity_change' => -5,
            'stock_after' => 25,
        ]);
    }

    /**
     * Test order creation validation failures.
     */
    public function test_order_creation_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => '',
            'customer_email' => 'invalid-email',
            'items' => [],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Validation error.',
            ])
            ->assertJsonValidationErrors(['customer_name', 'customer_email', 'items']);
    }

    /**
     * Test order creation fails when insufficient stock with itemized breakdown.
     */
    public function test_order_creation_fails_when_stock_insufficient(): void
    {
        $product = Product::factory()->create([
            'name' => 'Eggs (12)',
            'stock_on_hand' => 2,
        ]);

        $payload = [
            'customer_name' => 'Thomas Anderson',
            'customer_email' => 'thomas@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Insufficient stock available for one or more requested items.',
                'errors' => [
                    'stock' => [
                        [
                            'product_id' => $product->id,
                            'product_name' => 'Eggs (12)',
                            'requested_quantity' => 10,
                            'stock_on_hand' => 2,
                            'shortage' => 8,
                        ],
                    ],
                ],
            ]);

        // Verify stock remains untouched
        $this->assertEquals(2, $product->fresh()->stock_on_hand);
    }

    /**
     * Test customer order history via email in query string at GET /api/v1/customers/orders.
     */
    public function test_can_fetch_order_history_using_email_in_query_string(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'thomas@example.com',
            'name' => 'Thomas Anderson',
        ]);

        Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'completed',
            'grand_total' => 150.00,
            'created_at' => now()->subDays(2),
        ]);

        Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'completed',
            'grand_total' => 300.00,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/customers/orders?email=thomas@example.com');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Customer order history retrieved successfully.',
            ])
            ->assertJsonPath('customer.email', 'thomas@example.com')
            ->assertJsonPath('pagination.total', 2);
    }

    /**
     * Test order history with query filters (status, date, min total).
     */
    public function test_customer_order_history_filters(): void
    {
        $customer = Customer::factory()->create(['email' => 'sarah@example.com']);

        $completedOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'completed',
            'grand_total' => 500.00,
            'created_at' => now()->subDay(),
        ]);

        Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
            'grand_total' => 100.00,
            'created_at' => now(),
        ]);

        // Filter for completed orders >= 200
        $response = $this->getJson('/api/v1/customers/orders?email=sarah@example.com&status=completed&min_total=200');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id', $completedOrder->id);
    }

    /**
     * Test order history returns 422 when email query param is missing or invalid.
     */
    public function test_order_history_requires_valid_email(): void
    {
        // Missing email
        $response = $this->getJson('/api/v1/customers/orders');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Invalid email format
        $response = $this->getJson('/api/v1/customers/orders?email=invalid-email-format');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test order history returns 404 when customer email does not exist.
     */
    public function test_order_history_for_nonexistent_customer_returns_404(): void
    {
        $response = $this->getJson('/api/v1/customers/orders?email=ghost@example.com');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
            ]);
    }

    /**
     * Test fetching low stock products below default and configurable thresholds at GET /api/v1/products/low-stock.
     */
    public function test_can_fetch_low_stock_products(): void
    {
        Product::factory()->create([
            'name' => 'Critical Item',
            'stock_on_hand' => 2,
            'low_stock_threshold' => 10,
        ]);

        Product::factory()->create([
            'name' => 'High Stock Item',
            'stock_on_hand' => 100,
            'low_stock_threshold' => 10,
        ]);

        // Default query
        $response = $this->getJson('/api/v1/products/low-stock');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Low stock inventory products retrieved successfully.',
            ])
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.name', 'Critical Item');

        // Query with custom threshold = 150 (both items should match)
        $customResponse = $this->getJson('/api/v1/products/low-stock?threshold=150');

        $customResponse->assertStatus(200)
            ->assertJsonPath('pagination.total', 2);
    }
}
