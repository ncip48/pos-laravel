<?php

namespace App\Repositories\Contracts;

use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;

interface StockRepositoryInterface
{
    /**
     * Lock and fetch the stock_levels row for (product, warehouse) for
     * update within a transaction, creating it with quantity=0 first if it
     * doesn't exist yet. MUST be called inside a DB transaction — this is
     * what prevents two concurrent sales (e.g. an online sale and a syncing
     * offline sale arriving at nearly the same time) from both reading the
     * same stale quantity and both decrementing from it.
     */
    public function lockForUpdate(int $productId, int $warehouseId): StockLevel;

    public function getQuantity(int $productId, int $warehouseId): int;

    public function setQuantity(StockLevel $stockLevel, int $newQuantity): StockLevel;

    public function recordMovement(array $attributes): StockMovement;

    public function movementsForProduct(int $productId, ?int $warehouseId = null, int $perPage = 25);

    public function recentMovements(int $limit = 50): Collection;
}
