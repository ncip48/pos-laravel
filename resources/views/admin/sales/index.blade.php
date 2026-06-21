@extends('layouts.admin')

@section('page-title', 'Transactions')
@section('breadcrumb', 'Sales History')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-green-light text-primary-green flex items-center justify-center">
                    <x-icon name="receipt" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Transactions</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $sales->total() }} transactions</span>
                        @unless (auth()->user()->can('sales.view-all'))
                            <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                            <span class="text-xs opacity-60">Showing your transactions only</span>
                        @endunless
                        <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $sales->where('status', 'completed')->count() }} completed
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                {{-- Quick Stats --}}
                <div class="flex items-center gap-4 text-sm bg-card rounded-xl border border-theme px-4 py-2">
                    <div>
                        <span class="text-secondary">Total Revenue</span>
                        <span class="font-bold text-primary font-mono-num ml-2">
                            {{ \App\Support\Money::fromAmount($sales->sum(fn($s) => $s->total()->amount()))->formatted() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET"
            class="bg-card rounded-2xl border border-theme p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="barcode" class="w-4 h-4" />
                    </div>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search invoice #..."
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="check-circle" class="w-4 h-4" />
                    </div>
                    <select name="status"
                        class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition appearance-none cursor-pointer">
                        <option value="">All statuses</option>
                        <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
                        <option value="refunded" @selected(($filters['status'] ?? '') === 'refunded')>Refunded</option>
                        <option value="partially_refunded" @selected(($filters['status'] ?? '') === 'partially_refunded')>Partially Refunded</option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="warehouse" class="w-4 h-4" />
                    </div>
                    <select name="warehouse_id"
                        class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition appearance-none cursor-pointer">
                        <option value="">All warehouses</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="calendar" class="w-4 h-4" />
                    </div>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" placeholder="From"
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="calendar" class="w-4 h-4" />
                    </div>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" placeholder="To"
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-green hover:bg-primary-green-dark text-white text-sm font-medium px-4 py-2.5 transition shadow-sm hover:shadow-md">
                        <x-icon name="filter" class="w-4 h-4" />
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status', 'warehouse_id', 'from', 'to', 'deviation_only']))
                        <a href="{{ route('admin.sales.index') }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                            <x-icon name="refresh" class="w-4 h-4" />
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            {{-- Additional Filters --}}
            <div class="mt-3 flex flex-wrap items-center gap-4 pt-3 border-t border-theme">
                @if (auth()->user()->can('pos-sync-audits.view'))
                    <label class="flex items-center gap-2.5 text-sm text-secondary cursor-pointer group">
                        <input type="checkbox" id="deviation-only-toggle" @checked($filters['deviation_only'] ?? false)
                            class="w-4 h-4 rounded border-theme text-primary-green focus:ring-primary-green focus:ring-2 transition">
                        <span class="group-hover:text-primary transition">Show price deviations</span>
                        <span class="text-xs text-secondary opacity-60">(Synced offline at different prices)</span>
                        <x-icon name="exclamation" class="w-4 h-4 text-amber-500" />
                    </label>
                @endif

                @if ($sales->total() > 0)
                    <span class="text-xs text-secondary">
                        <span class="font-medium text-primary">{{ $sales->total() }}</span> results found
                    </span>
                @endif
            </div>
        </form>

        {{-- Transactions Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-primary-green-light/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="receipt" class="w-3.5 h-3.5" />
                                    Invoice
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="clock" class="w-3.5 h-3.5" />
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
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                    Status
                                </span>
                            </th>
                            <th
                                class="px-6 py-3.5 text-center font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-center gap-1.5">
                                    <x-icon name="settings" class="w-3.5 h-3.5" />
                                    Actions
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme">
                        @forelse ($sales as $sale)
                            @php
                                $statusColors = [
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    'refunded' => 'warning',
                                    'partially_refunded' => 'warning',
                                ];
                                $isDeviation =
                                    $sale->was_created_offline &&
                                    $sale->items->contains(
                                        fn($item) => $item->unit_price_cents !== $item->product->selling_price_cents,
                                    );
                            @endphp
                            <tr class="hover:bg-primary-green-light/5 transition group cursor-pointer"
                                onclick="window.location='{{ route('admin.sales.show', $sale) }}'">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-primary-green-light/20 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="receipt" class="w-3.5 h-3.5 text-primary-green" />
                                        </div>
                                        <span
                                            class="font-mono-num font-semibold text-primary-green">{{ $sale->invoice_number }}</span>
                                        @if ($sale->was_created_offline)
                                            <span title="Synced from an offline sale" class="flex-shrink-0">
                                                <x-icon name="exclamation" class="w-3.5 h-3.5 text-amber-500" />
                                            </span>
                                        @endif
                                        @if ($isDeviation)
                                            <span title="Price deviation detected" class="flex-shrink-0">
                                                <x-icon name="alert-triangle" class="w-3.5 h-3.5 text-amber-500" />
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-secondary text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <x-icon name="calendar" class="w-3 h-3 text-secondary opacity-40" />
                                            {{ $sale->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-secondary opacity-60 mt-0.5">
                                            <x-icon name="clock" class="w-3 h-3" />
                                            {{ $sale->created_at->format('g:i A') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded-full bg-primary-green-light text-primary-green flex items-center justify-center text-xs font-medium flex-shrink-0">
                                            {{ $sale->customer->is_guest ? 'W' : substr($sale->customer->name, 0, 1) }}
                                        </div>
                                        <span
                                            class="text-secondary">{{ $sale->customer->is_guest ? 'Walk-in' : $sale->customer->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-secondary">{{ $sale->cashier->name }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary-green-light/20 text-xs font-medium text-secondary">
                                        <x-icon name="warehouse" class="w-3 h-3" />
                                        {{ $sale->warehouse->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num font-semibold text-primary">
                                    {{ $sale->total()->formatted() }}
                                    @if ($sale->items->count() > 0)
                                        <div class="text-xs text-secondary font-normal">
                                            {{ $sale->items->count() }} items
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$statusColors[$sale->status->value]">
                                        <span class="flex items-center gap-1.5">
                                            @if ($sale->status->value === 'completed')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            @endif
                                            {{ $sale->status->label() }}
                                        </span>
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ route('admin.sales.show', $sale) }}"
                                            class="p-1.5 rounded-lg text-secondary hover:bg-primary-green-light hover:text-primary-green transition"
                                            title="View Details">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </a>
                                        {{-- @if ($sale->status->value === 'completed')
                                            <button onclick="event.stopPropagation(); window.print();"
                                                class="p-1.5 rounded-lg text-secondary hover:bg-primary-green-light hover:text-primary-green transition"
                                                title="Print Receipt">
                                                <x-icon name="printer" class="w-4 h-4" />
                                            </button>
                                        @endif --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-20 h-20 rounded-2xl bg-primary-green-light/20 flex items-center justify-center mb-4">
                                            <x-icon name="receipt" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No transactions found</p>
                                        <p class="text-sm text-secondary mt-1">
                                            @if (request()->hasAny(['search', 'status', 'warehouse_id', 'from', 'to', 'deviation_only']))
                                                Try adjusting your search filters
                                            @else
                                                Start making sales to see transactions here
                                            @endif
                                        </p>

                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sales->hasPages())
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ $sales->firstItem() ?? 0 }}</span>
                        to <span class="font-medium text-primary">{{ $sales->lastItem() ?? 0 }}</span>
                        of <span class="font-medium text-primary">{{ $sales->total() }}</span> transactions
                    </div>
                    <div>
                        {{ $sales->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Deviation toggle handler
            const deviationToggle = document.getElementById('deviation-only-toggle');
            if (deviationToggle) {
                deviationToggle.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    if (this.checked) {
                        url.searchParams.set('deviation_only', '1');
                    } else {
                        url.searchParams.delete('deviation_only');
                    }
                    window.location.href = url.toString();
                });
            }

            // Auto-submit filters on select change (optional)
            document.querySelectorAll('select[name="status"], select[name="warehouse_id"]').forEach(select => {
                select.addEventListener('change', function() {
                    if (this.value) {
                        this.closest('form').submit();
                    }
                });
            });
        });
    </script>
@endpush
