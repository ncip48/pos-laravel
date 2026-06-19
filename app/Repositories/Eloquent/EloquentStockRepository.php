<?php

namespace App\Repositories\Eloquent;

use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentStockRepository implements StockRepositoryInterface
{
    public function __construct(
        private readonly StockLevel $stockLevelModel,
        private readonly StockMovement $stockMovementModel,
    ) {}

    public function lockForUpdate(int $productId, int $warehouseId): StockLevel
    {
        $stockLevel = $this->stockLevelModel->newQuery()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if ($stockLevel) {
            return $stockLevel;
        }

        // No row yet for this product/warehouse pair. Insert quantity=0 then
        // re-select with a lock — we can't lock a row that doesn't exist yet,
        // and a plain create() here would race with a concurrent insert under
        // load, so we rely on the unique(product_id, warehouse_id) constraint
        // and recover from a duplicate-key race by re-selecting with a lock.
        try {
            $this->stockLevelModel->newQuery()->create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => 0,
                'updated_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Duplicate key = another concurrent request created it first.
            // That's fine — fall through to the locked re-select below.
            if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                throw $e;
            }
        }

        return $this->stockLevelModel->newQuery()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function getQuantity(int $productId, int $warehouseId): int
    {
        return (int) $this->stockLevelModel->newQuery()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    public function setQuantity(StockLevel $stockLevel, int $newQuantity): StockLevel
    {
        $stockLevel->forceFill([
            'quantity' => $newQuantity,
            'updated_at' => now(),
        ])->save();

        return $stockLevel;
    }

    public function recordMovement(array $attributes): StockMovement
    {
        $attributes['created_at'] ??= now();

        return $this->stockMovementModel->newQuery()->create($attributes);
    }

    public function movementsForProduct(int $productId, ?int $warehouseId = null, int $perPage = 25)
    {
        return $this->stockMovementModel->newQuery()
            ->where('product_id', $productId)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->with(['warehouse', 'user', 'reference'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function recentMovements(int $limit = 50): Collection
    {
        return $this->stockMovementModel->newQuery()
            ->with(['product', 'warehouse', 'user'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
