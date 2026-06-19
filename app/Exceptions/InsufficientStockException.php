<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by StockService ONLY for online/admin-initiated stock decrements
 * (e.g. an in-browser POS sale ringing up live, a manual stock adjustment)
 * where blocking on insufficient stock is the correct, safe behavior.
 *
 * It is deliberately NEVER thrown during offline-sale sync — per product
 * requirements, an offline sale that already happened in the physical store
 * must be accepted and allowed to push stock negative, not rejected after
 * the fact. See SaleSyncService::sync() and StockService::decrementAllowingNegative().
 */
class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $productId,
        public readonly int $warehouseId,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "Insufficient stock for product #{$productId} in warehouse #{$warehouseId}: " .
                "requested {$requested}, available {$available}."
        );
    }
}
