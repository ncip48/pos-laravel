<?php

namespace App\Providers;

use App\Events\SaleCompleted;
use App\Events\SaleRefunded;
use App\Events\StockLevelLow;
use App\Listeners\LogSaleActivity;
use App\Listeners\NotifyLowStock;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        StockLevelLow::class => [
            NotifyLowStock::class,
        ],
        SaleCompleted::class => [
            LogSaleActivity::class,
        ],
        SaleRefunded::class => [
            // Extension point: e.g. NotifyManagerOfRefund::class
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
