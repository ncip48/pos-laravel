<?php

namespace App\Repositories\Eloquent;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentSaleRepository extends BaseRepository implements SaleRepositoryInterface
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }

    public function findByClientUuid(string $clientUuid): ?Sale
    {
        return $this->model->newQuery()
            ->withTrashed() // a replayed sync must still find a cancelled/soft-deleted sale to stay idempotent
            ->where('client_uuid', $clientUuid)
            ->first();
    }

    /**
     * Generates "INV-{year}-{sequence}", sequence padded to 6 digits,
     * scoped per calendar year. Uses a row lock on the last invoice of the
     * year to stay correct under concurrent checkouts; still wrap the
     * caller in a transaction (PosService does this).
     */
    public function nextInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        $last = $this->model->newQuery()
            ->withTrashed()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->first();

        $nextSequence = $last
            ? ((int) substr($last->invoice_number, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
    }

    public function paginateWithFilters(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginate($perPage, ['customer', 'cashier', 'warehouse'], function (Builder $query) use ($filters) {
            if (!empty($filters['search'])) {
                $query->where('invoice_number', 'like', "%{$filters['search']}%");
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }
            if (!empty($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }
            if (!empty($filters['cashier_id'])) {
                $query->where('user_id', $filters['cashier_id']);
            }
            if (!empty($filters['from'])) {
                $query->whereDate('created_at', '>=', $filters['from']);
            }
            if (!empty($filters['to'])) {
                $query->whereDate('created_at', '<=', $filters['to']);
            }
            if (!empty($filters['deviation_only'])) {
                $query->where('has_price_deviation', true);
            }

            $query->orderByDesc('created_at');
        });
    }

    public function salesBetween(\DateTimeInterface $from, \DateTimeInterface $to, ?int $warehouseId = null): Collection
    {
        return $this->model->newQuery()
            ->with(['items', 'payments', 'customer'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->when($warehouseId, fn(Builder $q) => $q->where('warehouse_id', $warehouseId))
            ->get();
    }

    public function dailyTotals(\DateTimeInterface $from, \DateTimeInterface $to, ?int $warehouseId = null): Collection
    {
        return $this->model->newQuery()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(total_cents) as total_cents')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->when($warehouseId, fn(Builder $q) => $q->where('warehouse_id', $warehouseId))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function recentTransactions(int $limit = 10, ?int $warehouseId = null): Collection
    {
        return $this->model->newQuery()
            ->with(['customer', 'cashier'])
            ->when($warehouseId, fn(Builder $q) => $q->where('warehouse_id', $warehouseId))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function sumTotalsBetween(\DateTimeInterface $from, \DateTimeInterface $to, ?int $warehouseId = null): int
    {
        return (int) $this->model->newQuery()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->when($warehouseId, fn(Builder $q) => $q->where('warehouse_id', $warehouseId))
            ->sum('total_cents');
    }
}
