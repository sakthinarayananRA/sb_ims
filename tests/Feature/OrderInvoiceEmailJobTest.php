<?php

namespace Tests\Feature;

use App\Jobs\SendOrderInvoiceEmailJob;
use App\Mail\OrderInvoiceMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderInvoiceEmailJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that creating an order model event dispatches SendOrderInvoiceEmailJob to queue.
     */
    public function test_order_creation_dispatches_background_invoice_job_via_model_event_listener(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create([
            'email' => 'arthur.shelby@example.com',
            'name' => 'Arthur Shelby',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20260910-TEST1',
            'customer_id' => $customer->id,
            'status' => 'completed',
            'subtotal' => 100.00,
            'tax_amount' => 8.00,
            'grand_total' => 108.00,
            'paid_amount' => 150.00,
            'change_amount' => 42.00,
        ]);

        // Assert job was pushed to the queue
        Queue::assertPushed(SendOrderInvoiceEmailJob::class, function (SendOrderInvoiceEmailJob $job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    /**
     * Test that SendOrderInvoiceEmailJob sends the OrderInvoiceMail to customer email.
     */
    public function test_invoice_job_sends_email_to_customer(): void
    {
        Mail::fake();

        $customer = Customer::factory()->create([
            'email' => 'polly.gray@example.com',
            'name' => 'Polly Gray',
        ]);

        $product = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'price_per_unit' => 50.00,
            'tax_percentage' => 8.00,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20260910-POLLY',
            'customer_id' => $customer->id,
            'status' => 'completed',
            'subtotal' => 100.00,
            'tax_amount' => 8.00,
            'grand_total' => 108.00,
            'paid_amount' => 200.00,
            'change_amount' => 92.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'tax_percentage' => 8.00,
            'subtotal' => 100.00,
            'tax_amount' => 8.00,
            'total' => 108.00,
        ]);

        // Execute the queued job synchronously
        $job = new SendOrderInvoiceEmailJob($order);
        $job->handle();

        // Assert mail was sent to polly.gray@example.com
        Mail::assertSent(OrderInvoiceMail::class, function (OrderInvoiceMail $mail) use ($order) {
            return $mail->hasTo('polly.gray@example.com') &&
                   $mail->order->order_number === $order->order_number;
        });
    }

    /**
     * Test full API endpoint POST /api/v1/orders dispatches background email job.
     */
    public function test_api_order_store_dispatches_email_job(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'code' => 'PRD-COLG-01',
            'price_per_unit' => 50.00,
            'tax_percentage' => 8.00,
            'stock_on_hand' => 25,
            'low_stock_threshold' => 5,
        ]);

        $payload = [
            'customer_name' => 'Ada Thorne',
            'customer_email' => 'ada.thorne@example.com',
            'paid_amount' => 200.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201);

        Queue::assertPushed(SendOrderInvoiceEmailJob::class, function (SendOrderInvoiceEmailJob $job) {
            return $job->order->customer->email === 'ada.thorne@example.com';
        });
    }
}

