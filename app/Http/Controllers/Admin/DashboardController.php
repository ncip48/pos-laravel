<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DashboardController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public static function middleware(): array
    {
        return [
            // Use the absolute class name with the permission parameter instead of the alias string
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('dashboard.view')),
        ];
    }

    public function index(): View
    {
        $warehouseId = request()->integer('warehouse_id') ?: null;

        $summary = $this->dashboardService->salesSummary($warehouseId);
        $revenueSeries = $this->dashboardService->revenueSeries(
            Carbon::now()->subDays(13),
            Carbon::now(),
            $warehouseId,
        );
        $lowStockItems = $this->dashboardService->lowStockAlerts($warehouseId, 8);
        $lowStockCount = $this->dashboardService->lowStockCount($warehouseId);
        $bestSelling = $this->dashboardService->bestSellingProducts($warehouseId, 5);
        $recentTransactions = $this->dashboardService->recentTransactions($warehouseId, 8);
        $paymentBreakdown = $this->dashboardService->paymentMethodBreakdown(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
            $warehouseId,
        );

        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.dashboard.index', compact(
            'summary',
            'revenueSeries',
            'lowStockItems',
            'lowStockCount',
            'bestSelling',
            'recentTransactions',
            'paymentBreakdown',
            'warehouses',
            'warehouseId',
        ));
    }
}
