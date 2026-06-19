<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySku(string $sku): ?Product;

    public function findByBarcode(string $barcode): ?Product;

    /**
     * Fast text search across name/sku/barcode for POS product lookup.
     * Eager-loads stockLevels scoped to the given warehouse so the POS
     * screen can show "in stock: N" without an N+1.
     */
    public function searchForPos(string $term, int $warehouseId, int $limit = 20): Collection;

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Products at or below their min_stock_level, optionally scoped to one
     * warehouse. Used by DashboardService and the low-stock report.
     */
    public function lowStock(?int $warehouseId = null): Collection;

    /**
     * Best-selling products by quantity sold within a date range. Used by
     * the dashboard and sales reports.
     */
    public function bestSelling(\DateTimeInterface $from, \DateTimeInterface $to, int $limit = 10): Collection;

    public function skuExists(string $sku, ?int $excludeId = null): bool;

    public function barcodeExists(string $barcode, ?int $excludeId = null): bool;
}
