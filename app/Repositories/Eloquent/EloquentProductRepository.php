<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\StockLevel;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->newQuery()->where('sku', $sku)->first();
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return $this->model->newQuery()->where('barcode', $barcode)->first();
    }

    public function searchForPos(string $term, int $warehouseId, int $limit = 20): Collection
    {
        return $this->model->newQuery()
            ->active()
            ->search($term)
            ->with(['unit', 'category'])
            ->with(['stockLevels' => fn($q) => $q->where('warehouse_id', $warehouseId)])
            ->limit($limit)
            ->get();
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paginate($perPage, ['category', 'unit'], function (Builder $query) use ($filters) {
            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }

            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['low_stock_only'])) {
                // whereHas() runs as a correlated subquery, so whereColumn()
                // *inside* it CAN compare the related table's column against
                // the outer products table — this is the one context where
                // that works, unlike a plain join-less top-level query.
                $query->whereHas('stockLevels', function (Builder $q) use ($filters) {
                    $q->whereColumn('stock_levels.quantity', '<=', 'products.min_stock_level');

                    if (!empty($filters['warehouse_id'])) {
                        $q->where('stock_levels.warehouse_id', $filters['warehouse_id']);
                    }
                })->where('track_stock', true);
            }

            $query->orderBy($filters['sort_by'] ?? 'name', $filters['sort_dir'] ?? 'asc');
        });
    }

    public function lowStock(?int $warehouseId = null): Collection
    {
        $query = StockLevel::query()->lowStock()->with(['product.unit', 'warehouse']);

        if ($warehouseId) {
            $query->where('stock_levels.warehouse_id', $warehouseId);
        }

        return $query->get();
    }

    public function bestSelling(\DateTimeInterface $from, \DateTimeInterface $to, int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->select('products.*')
            ->selectRaw('SUM(sale_items.quantity) as total_quantity_sold')
            ->selectRaw('SUM(sale_items.total_cents) as total_revenue_cents')
            ->join('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->groupBy('products.id')
            ->orderByDesc('total_quantity_sold')
            ->limit($limit)
            ->get();
    }

    public function skuExists(string $sku, ?int $excludeId = null): bool
    {
        return $this->model->newQuery()
            ->where('sku', $sku)
            ->when($excludeId, fn(Builder $q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function barcodeExists(string $barcode, ?int $excludeId = null): bool
    {
        return $this->model->newQuery()
            ->where('barcode', $barcode)
            ->when($excludeId, fn(Builder $q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
