<?php

namespace App\Observers;

use App\Jobs\SendOrderInvoiceEmailJob;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" model event.
     * Dispatches the queued background email job after database commit.
     */
    public function created(Order $order): void
    {
        Log::info("Order model 'created' event triggered for Order #{$order->order_number} (ID: {$order->id}). Dispatching background invoice email job.");

        SendOrderInvoiceEmailJob::dispatch($order)->afterCommit();
    }
}

