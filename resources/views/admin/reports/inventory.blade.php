@extends('layouts.admin')

@section('page-title', 'Inventory Report')
@section('breadcrumb', 'Reports')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="inbox" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Inventory Report</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>Stock overview and valuation</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ $report['totals']['product_count'] }} products tracked
                        </span>
                    </div>
                </div>
            </div>
            @can('reports.export')
                <div class="flex gap-2">
                    <a href="{{ route('admin.reports.inventory.export', ['format' => 'csv'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file-text" class="w-4 h-4" />
                        CSV
                    </a>
                    <a href="{{ route('admin.reports.inventory.export', ['format' => 'xlsx'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file" class="w-4 h-4" />
                        Excel
                    </a>
                    <a href="{{ route('admin.reports.inventory.export', ['format' => 'pdf'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file-text" class="w-4 h-4" />
                        PDF
                    </a>
                </div>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-card rounded-2xl border border-theme p-5 shadow-sm hover:shadow-md transition-shadow">
            @include('admin.reports._filters', ['route' => 'admin.reports.inventory'])
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Products Tracked</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">{{ $report['totals']['product_count'] }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Low Stock Items</p>
                <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-1 font-mono-num">
                    {{ $report['totals']['low_stock_count'] }}
                </p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Stock Value (at cost)</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">Rp {{ number_format($report['totals']['total_value_at_cost'], 0) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Stock Value (at price)</p>
                <p class="text-lg font-bold text-sage-600 dark:text-sage-400 mt-1 font-mono-num">Rp {{ number_format($report['totals']['total_value_at_price'], 0) }}</p>
            </div>
        </div>

        {{-- Movement Summary --}}
        @if ($report['movement_summary']->isNotEmpty())
            <div class="bg-card rounded-2xl border border-theme p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                        <x-icon name="clock" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Movement Activity in Period</h3>
                    <span class="text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">
                        {{ $report['movement_summary']->count() }} types
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    @foreach ($report['movement_summary'] as $type => $total)
                        <div class="bg-sage-50/50 dark:bg-sage-900/20 rounded-xl px-4 py-3 border border-theme">
                            <p class="text-secondary text-xs font-medium uppercase tracking-wider">{{ ucwords(str_replace('_', ' ', $type)) }}</p>
                            <p class="font-mono-num font-semibold text-primary text-lg mt-0.5">{{ $total }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Report Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="px-6 py-4 border-b border-theme flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                        <x-icon name="cube" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Stock Details</h3>
                    <span class="text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">
                        {{ count($report['stock_rows']) }} products
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-secondary">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                        In stock
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Low stock
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        Out of stock
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
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="barcode" class="w-3.5 h-3.5" />
                                    SKU
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="inbox" class="w-3.5 h-3.5" />
                                    Quantity
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="trending-down" class="w-3.5 h-3.5" />
                                    Value (cost)
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="trending-up" class="w-3.5 h-3.5" />
                                    Value (price)
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
                        @forelse ($report['stock_rows'] as $row)
                            <tr class="hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-sage-100/50 dark:bg-sage-800/30 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                            <x-icon name="cube" class="w-4 h-4 text-sage-600 dark:text-sage-400" />
                                        </div>
                                        <span class="font-medium text-primary">{{ $row['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono-num text-secondary">
                                    {{ $row['sku'] }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num">
                                    @if ($row['quantity'] == 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800">
                                            Out of stock
                                        </span>
                                    @elseif ($row['is_low_stock'])
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            {{ $row['quantity'] }}
                                        </span>
                                    @else
                                        <span class="text-primary font-medium">{{ $row['quantity'] }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num text-secondary">
                                    Rp {{ number_format($row['stock_value_at_cost'], 0) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num text-sage-600 dark:text-sage-400">
                                    Rp {{ number_format($row['stock_value_at_price'], 0) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if (isset($row['product_id']))
                                        {{-- <a href="{{ route('admin.products.show', $row['product_id']) }}"
                                            class="p-1.5 rounded-lg text-secondary hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 transition"
                                            title="View Product">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </a> --}}
                                    @else
                                        <span class="text-xs text-secondary opacity-30">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-2xl bg-sage-100/30 dark:bg-sage-800/20 flex items-center justify-center mb-4">
                                            <x-icon name="inbox" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No tracked products found</p>
                                        <p class="text-sm text-secondary mt-1">Try adjusting your filters or add products to inventory</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (count($report['stock_rows']) > 0)
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ count($report['stock_rows']) }}</span> products
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-secondary">
                            <span class="font-medium text-primary">Total Cost Value:</span>
                            <span class="font-mono-num font-bold text-primary">Rp {{ number_format($report['totals']['total_value_at_cost'], 0) }}</span>
                        </span>
                        <span class="text-secondary">
                            <span class="font-medium text-primary">Total Price Value:</span>
                            <span class="font-mono-num font-bold text-sage-600 dark:text-sage-400">Rp {{ number_format($report['totals']['total_value_at_price'], 0) }}</span>
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
