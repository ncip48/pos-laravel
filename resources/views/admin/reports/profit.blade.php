@extends('layouts.admin')

@section('page-title', 'Profit Report')
@section('breadcrumb', 'Reports')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="chart-pie" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Profit Report</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>Product profitability analysis</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ count($report['rows']) }} products sold
                        </span>
                    </div>
                </div>
            </div>
            @can('reports.export')
                <div class="flex gap-2">
                    <a href="{{ route('admin.reports.profit.export', ['format' => 'csv'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file-text" class="w-4 h-4" />
                        CSV
                    </a>
                    <a href="{{ route('admin.reports.profit.export', ['format' => 'xlsx'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file" class="w-4 h-4" />
                        Excel
                    </a>
                    <a href="{{ route('admin.reports.profit.export', ['format' => 'pdf'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file-text" class="w-4 h-4" />
                        PDF
                    </a>
                </div>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-card rounded-2xl border border-theme p-5 shadow-sm hover:shadow-md transition-shadow">
            @include('admin.reports._filters', ['route' => 'admin.reports.profit'])
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Revenue</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">Rp {{ number_format($report['totals']['revenue'], 0) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Cost of Goods Sold</p>
                <p class="text-lg font-bold text-red-600 dark:text-red-400 mt-1 font-mono-num">Rp {{ number_format($report['totals']['cost'], 0) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Gross Profit</p>
                <p class="text-lg font-bold text-sage-600 dark:text-sage-400 mt-1 font-mono-num">Rp {{ number_format($report['totals']['profit'], 0) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Margin</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">{{ $report['totals']['margin_percent'] }}%</p>
            </div>
        </div>

        {{-- Report Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="px-6 py-4 border-b border-theme flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                        <x-icon name="cube" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Product Profitability</h3>
                    <span class="text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">
                        {{ count($report['rows']) }} products
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-secondary">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                        Positive margin
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        Negative margin
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-sage-50 dark:bg-sage-900/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="tag" class="w-3.5 h-3.5" />
                                    Product
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="inbox" class="w-3.5 h-3.5" />
                                    Qty Sold
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="cash" class="w-3.5 h-3.5" />
                                    Revenue
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="trending-down" class="w-3.5 h-3.5" />
                                    Cost (COGS)
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="trending-up" class="w-3.5 h-3.5" />
                                    Profit
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="chart-pie" class="w-3.5 h-3.5" />
                                    Margin
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-center font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-center gap-1.5">
                                    <x-icon name="settings" class="w-3.5 h-3.5" />
                                    Actions
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme">
                        @forelse ($report['rows'] as $row)
                            <tr class="hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-sage-100/50 dark:bg-sage-800/30 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                            <x-icon name="cube" class="w-4 h-4 text-sage-600 dark:text-sage-400" />
                                        </div>
                                        <span class="font-medium text-primary">{{ $row['product_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num text-secondary">
                                    {{ $row['quantity_sold'] }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num text-primary">
                                    Rp {{ number_format($row['revenue'], 0) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num text-red-600 dark:text-red-400">
                                    Rp {{ number_format($row['cost'], 0) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num font-semibold
                                    @if ($row['profit'] < 0) text-red-600 dark:text-red-400
                                    @else text-sage-600 dark:text-sage-400 @endif">
                                    Rp {{ number_format($row['profit'], 0) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-medium
                                        @if ($row['margin_percent'] >= 40) bg-sage-100/50 dark:bg-sage-800/30 text-sage-700 dark:text-sage-300 border border-sage-200 dark:border-sage-700
                                        @elseif($row['margin_percent'] >= 20) bg-sage-50/50 dark:bg-sage-900/20 text-sage-600 dark:text-sage-400 border border-theme
                                        @elseif($row['margin_percent'] >= 0) bg-amber-50/50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                        @else bg-red-50/50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 @endif">
                                        {{ $row['margin_percent'] }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if (isset($row['product_id']))
                                        <a href="{{ route('admin.products.show', $row['product_id']) }}"
                                            class="p-1.5 rounded-lg text-secondary hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 transition"
                                            title="View Product">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </a>
                                    @else
                                        <span class="text-xs text-secondary opacity-30">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-2xl bg-sage-100/30 dark:bg-sage-800/20 flex items-center justify-center mb-4">
                                            <x-icon name="chart-pie" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No sales in this period</p>
                                        <p class="text-sm text-secondary mt-1">Try adjusting your date range or filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (count($report['rows']) > 0)
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ count($report['rows']) }}</span> products
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-secondary">
                            <span class="font-medium text-primary">Total Revenue:</span>
                            <span class="font-mono-num font-bold text-primary">Rp {{ number_format($report['totals']['revenue'], 0) }}</span>
                        </span>
                        <span class="text-secondary">
                            <span class="font-medium text-primary">Total Profit:</span>
                            <span class="font-mono-num font-bold text-sage-600 dark:text-sage-400">Rp {{ number_format($report['totals']['profit'], 0) }}</span>
                        </span>
                        <span class="text-secondary">
                            <span class="font-medium text-primary">Avg. Margin:</span>
                            <span class="font-mono-num font-bold text-primary">{{ $report['totals']['margin_percent'] }}%</span>
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
