<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SaleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Look up a sale by its client-generated idempotency key. This is the
     * crux of safe offline sync: before SaleSyncService creates anything,
     * it checks this — if a sale with this client_uuid already exists, the
     * sync is a safe no-op replay, not a duplicate.
     */
    public function findByClientUuid(string $clientUuid): ?Sale;

    public function nextInvoiceNumber(): string;

    public function paginateWithFilters(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function salesBetween(\DateTimeInterface $from, \DateTimeInterface $to, ?int $warehouseId = null): Collection;

    public function dailyTotals(\DateTimeInterface $from, \DateTimeInterface $to, ?int $warehouseId = null): Collection;

    public function recentTransactions(int $limit = 10, ?int $warehouseId = null): Collection;

    public function sumTotalsBetween(\DateTimeInterface $from, \DateTimeInterface $to, ?int $warehouseId = null): int;
}
