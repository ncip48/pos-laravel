@extends('layouts.admin')

@section('page-title', 'Sales Report')
@section('breadcrumb', 'Reports')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="chart-bar" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Sales Report</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>Transaction summary and analytics</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ $report['totals']['transaction_count'] }} transactions
                        </span>
                    </div>
                </div>
            </div>
            @can('reports.export')
                <div class="flex gap-2">
                    <a href="{{ route('admin.reports.sales.export', ['format' => 'csv'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file-text" class="w-4 h-4" />
                        CSV
                    </a>
                    <a href="{{ route('admin.reports.sales.export', ['format' => 'xlsx'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file" class="w-4 h-4" />
                        Excel
                    </a>
                    <a href="{{ route('admin.reports.sales.export', ['format' => 'pdf'] + request()->query()) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="file-text" class="w-4 h-4" />
                        PDF
                    </a>
                </div>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-card rounded-2xl border border-theme p-5 shadow-sm hover:shadow-md transition-shadow">
            @include('admin.reports._filters', ['route' => 'admin.reports.sales'])
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Transactions</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">{{ $report['totals']['transaction_count'] }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Subtotal</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">Rp {{ number_format($report['totals']['subtotal']) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Discounts</p>
                <p class="text-lg font-bold text-red-600 dark:text-red-400 mt-1 font-mono-num">Rp {{ number_format($report['totals']['discount']) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Tax Collected</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">Rp {{ number_format($report['totals']['tax']) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Total Revenue</p>
                <p class="text-lg font-bold text-sage-600 dark:text-sage-400 mt-1 font-mono-num">Rp {{ number_format($report['totals']['total']) }}</p>
            </div>
        </div>

        {{-- Report Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="px-6 py-4 border-b border-theme flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                        <x-icon name="receipt" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Transaction Details</h3>
                    <span class="text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">
                        {{ count($report['rows']) }} records
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-secondary">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                        Total: Rp {{ number_format(collect($report['rows'])->sum('total')) }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-sage-50 dark:bg-sage-900/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="receipt" class="w-3.5 h-3.5" />
                                    Invoice
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="calendar" class="w-3.5 h-3.5" />
                                    Date
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="user" class="w-3.5 h-3.5" />
                                    Customer
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="user-check" class="w-3.5 h-3.5" />
                                    Cashier
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="warehouse" class="w-3.5 h-3.5" />
                                    Warehouse
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="cash" class="w-3.5 h-3.5" />
                                    Total
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
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-sage-100/50 dark:bg-sage-800/30 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="receipt" class="w-3.5 h-3.5 text-sage-600 dark:text-sage-400" />
                                        </div>
                                        <span class="font-mono-num font-semibold text-sage-600 dark:text-sage-400">
                                            {{ $row['invoice_number'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-secondary text-sm">
                                    <div class="flex items-center gap-1.5">
                                        <x-icon name="calendar" class="w-3.5 h-3.5 text-secondary opacity-40" />
                                        {{ $row['date'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-secondary">{{ $row['customer'] }}</td>
                                <td class="px-6 py-4 text-secondary">{{ $row['cashier'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sage-100/50 dark:bg-sage-800/30 text-xs font-medium text-sage-700 dark:text-sage-300 border border-sage-200 dark:border-sage-700">
                                        <x-icon name="warehouse" class="w-3 h-3" />
                                        {{ $row['warehouse'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num font-semibold text-primary">
                                    Rp {{ number_format($row['total']) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if (isset($row['id']))
                                        <a href="{{ route('admin.sales.show', $row['id']) }}"
                                            class="p-1.5 rounded-lg text-secondary hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 transition"
                                            title="View Transaction">
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
                                            <x-icon name="chart-bar" class="w-10 h-10 text-secondary opacity-30" />
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

            @if (isset($report['rows']) && count($report['rows']) > 0)
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ count($report['rows']) }}</span> transactions
                        <span class="text-xs opacity-60">({{ $report['totals']['transaction_count'] }} total)</span>
                    </div>
                    <div class="text-sm text-secondary">
                        <span class="font-medium text-primary">Total Revenue:</span>
                        <span class="font-mono-num font-bold text-sage-600 dark:text-sage-400">
                            Rp {{ number_format($report['totals']['total']) }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
