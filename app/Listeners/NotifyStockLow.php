<?php

namespace App\Listeners;

use App\Events\StockLevelLow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Queued so a slow notification channel (email/Slack/push) never adds
 * latency to the request that triggered the stock decrement — critical
 * for POS checkout, which must stay fast.
 */
class NotifyLowStock implements ShouldQueue
{
    public function handle(StockLevelLow $event): void
    {
        Log::channel('stack')->warning('Low stock alert', [
            'product_id' => $event->product->id,
            'product_name' => $event->product->name,
            'warehouse_id' => $event->warehouseId,
            'quantity' => $event->newQuantity,
            'min_stock_level' => $event->product->min_stock_level,
        ]);

        // Extension point: notify admins/managers via Laravel Notifications,
        // e.g. Notification::send($managers, new LowStockNotification($event));
        // Left as a log entry here since notification channels (mail/Slack/etc)
        // are an infrastructure choice outside this module's scope.
    }
}
