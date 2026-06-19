<?php

namespace App\Events;

use App\Models\SaleRefund;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleRefunded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SaleRefund $refund,
    ) {}
}
