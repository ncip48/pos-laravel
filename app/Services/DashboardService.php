<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates everything the dashboard page needs into a single call per
 * widget, each independently cacheable/testable. Deliberately NOT one
 * giant "getDashboardData()" god-method -- the dashboard Blade view calls
 * each method it needs, which keeps each query's purpose self-documenting
 * and means a future "embed just the low-stock widget elsewhere" need
 * doesn't require unpacking a monolithic payload.
 */
class DashboardService
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Headline numbers for the top of the dashboard: today vs yesterday,
     * this month vs last month -- the comparison is what makes a raw
     * number meaningful to a store owner at a glance.
     */
    public function salesSummary(?int $warehouseId = null): array
    {
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $yesterdayStart = Carbon::yesterday();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        $todayCents = $this->saleRepository->sumTotalsBetween($todayStart, $todayEnd, $warehouseId);
        $yesterdayCents = $this->saleRepository->sumTotalsBetween($yesterdayStart, $yesterdayEnd, $warehouseId);
        $monthCents = $this->saleRepository->sumTotalsBetween($monthStart, $monthEnd, $warehouseId);
        $lastMonthCents = $this->saleRepository->sumTotalsBetween($lastMonthStart, $lastMonthEnd, $warehouseId);

        $todayCount = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->count();

        return [
            'today_revenue' => Money::fromAmount($todayCents),
            'today_revenue_change_percent' => $this->percentChange($yesterdayCents, $todayCents),
            'today_transaction_count' => $todayCount,
            'month_revenue' => Money::fromAmount($monthCents),
            'month_revenue_change_percent' => $this->percentChange($lastMonthCents, $monthCents),
        ];
    }

    /**
     * Revenue + profit series for the dashboard chart, one row per day in
     * the range. Profit is computed from each sale_item's LOCKED
     * unit_cost_cents/unit_price_cents (never recomputed from current
     * catalog) so historical chart data never silently shifts when
     * today's cost prices change.
     */
    public function revenueSeries(Carbon $from, Carbon $to, ?int $warehouseId = null): array
    {
        $rows = DB::table('sales')
            ->selectRaw('DATE(sales.created_at) as date')
            ->selectRaw('SUM(sales.total_cents) as revenue_cents')
            ->selectRaw('SUM(sale_items.quantity * (sale_items.unit_price_cents - sale_items.unit_cost_cents)) as profit_cents')
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->when($warehouseId, fn($q) => $q->where('sales.warehouse_id', $warehouseId))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill in zero-value days so the chart doesn't have gaps for days
        // with no sales -- a flat line at 0 is more honest than a skipped point.
        $series = [];
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();
            $row = $rows->get($key);
            $series[] = [
                'date' => $key,
                'revenue' => $row ? round($row->revenue_cents, 2) : 0,
                'profit' => $row ? round($row->profit_cents, 2) : 0,
            ];
            $cursor->addDay();
        }

        return $series;
    }

    public function lowStockAlerts(?int $warehouseId = null, int $limit = 10)
    {
        return $this->productRepository->lowStock($warehouseId)->take($limit);
    }

    public function lowStockCount(?int $warehouseId = null): int
    {
        return $this->productRepository->lowStock($warehouseId)->count();
    }

    public function bestSellingProducts(?int $warehouseId = null, int $limit = 5)
    {
        $from = Carbon::now()->subDays(30);
        $to = Carbon::now();

        // bestSelling() doesn't natively filter by warehouse (it aggregates
        // sale_items globally); for a single-warehouse view this is an
        // acceptable simplification today, flagged here for whoever adds
        // per-warehouse bestseller filtering later.
        return $this->productRepository->bestSelling($from, $to, $limit);
    }

    public function recentTransactions(?int $warehouseId = null, int $limit = 8)
    {
        return $this->saleRepository->recentTransactions($limit, $warehouseId);
    }

    public function paymentMethodBreakdown(Carbon $from, Carbon $to, ?int $warehouseId = null): array
    {
        $rows = DB::table('sale_payments')
            ->selectRaw('sale_payments.method, SUM(sale_payments.amount_cents) as total_cents')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->when($warehouseId, fn($q) => $q->where('sales.warehouse_id', $warehouseId))
            ->groupBy('sale_payments.method')
            ->get();

        return $rows->map(fn($row) => [
            'method' => $row->method,
            'total' => Money::fromAmount((int) $row->total_cents),
        ])->all();
    }

    private function percentChange(int $previousCents, int $currentCents): float
    {
        if ($previousCents === 0) {
            return $currentCents > 0 ? 100.0 : 0.0;
        }

        return round((($currentCents - $previousCents) / $previousCents) * 100, 1);
    }
}
