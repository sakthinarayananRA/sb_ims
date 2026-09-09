<?php

namespace App\Jobs;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order
    ) {
        // Ensure the queued job is only dispatched after the database transaction has committed
        $this->afterCommit = true;
    }

    /**
     * Execute the job in the background.
     */
    public function handle(): void
    {
        // Ensure customer and order line items with products are fully hydrated
        $this->order->loadMissing(['customer', 'items.product']);

        $customerEmail = $this->order->customer?->email;

        if (empty($customerEmail)) {
            Log::warning("Skipping order invoice email: Customer email is missing for Order #{$this->order->order_number} (ID: {$this->order->id})");
            return;
        }

        Log::info("Dispatching invoice email to {$customerEmail} for Order #{$this->order->order_number}");

        Mail::to($customerEmail)->send(new OrderInvoiceMail($this->order));

        Log::info("Invoice email successfully delivered to mailer for Order #{$this->order->order_number}");
    }
}

