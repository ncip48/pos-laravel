<?php

namespace App\Repositories\Eloquent;

use App\Models\Purchase;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentPurchaseRepository extends BaseRepository implements PurchaseRepositoryInterface
{
    public function __construct(Purchase $model)
    {
        parent::__construct($model);
    }

    public function nextPurchaseNumber(): string
    {
        $year = now()->year;
        $prefix = "PO-{$year}-";

        $last = $this->model->newQuery()
            ->withTrashed()
            ->where('purchase_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('purchase_number')
            ->first();

        $nextSequence = $last
            ? ((int) substr($last->purchase_number, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paginate($perPage, ['supplier', 'warehouse', 'user'], function (Builder $query) use ($filters) {
            if (!empty($filters['search'])) {
                $query->where('purchase_number', 'like', "%{$filters['search']}%");
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['supplier_id'])) {
                $query->where('supplier_id', $filters['supplier_id']);
            }
            if (!empty($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            $query->orderByDesc('created_at');
        });
    }
}
