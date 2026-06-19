<?php

namespace App\Listeners;

use App\Events\SaleCompleted;

class LogSaleActivity
{
    public function handle(SaleCompleted $event): void
    {
        activity('sale')
            ->performedOn($event->sale)
            ->causedBy($event->sale->cashier)
            ->withProperties([
                'invoice_number' => $event->sale->invoice_number,
                'total_cents' => $event->sale->total_cents,
                'was_created_offline' => $event->sale->was_created_offline,
                'warehouse_id' => $event->sale->warehouse_id,
            ])
            ->log('Sale completed');
    }
}
