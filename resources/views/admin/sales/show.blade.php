@extends('layouts.admin')

@section('page-title', $sale->invoice_number)
@section('breadcrumb', 'Transaction Details')

@section('content')
    <div class="space-y-6">
        {{-- Header Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('admin.sales.index') }}"
                class="text-sm text-secondary hover:text-primary-green transition flex items-center gap-1.5 group">
                <x-icon name="chevron-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Back to Transactions
            </a>
            <div class="flex flex-wrap gap-2">
                @can('refund', $sale)
                    <button type="button" data-modal-target="refund-sale"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-sm font-medium px-4 py-2 text-white shadow-sm hover:shadow-md transition">
                        <x-icon name="refresh" class="w-4 h-4" />
                        Process Refund
                    </button>
                @endcan
                @can('cancel', $sale)
                    <button type="button" data-modal-target="cancel-sale"
                        class="inline-flex items-center gap-2 rounded-xl border border-red-300 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium px-4 py-2 transition">
                        <x-icon name="x" class="w-4 h-4" />
                        Cancel Sale
                    </button>
                @endcan
                <a href="{{ route('admin.sales.reprint', $sale) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    <x-icon name="printer" class="w-4 h-4" />
                    Print Receipt
                </a>
                <a href="{{ route('admin.sales.reprint-pdf', $sale) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    <x-icon name="file-text" class="w-4 h-4" />
                    Download PDF
                </a>
            </div>
        </div>

        {{-- Deviation Warning --}}
        @if ($sale->has_price_deviation)
            <div
                class="flex items-start gap-3 rounded-2xl bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-5 py-4">
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <x-icon name="alert-triangle" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Price Deviation Detected</p>
                    <p class="text-sm text-amber-700/80 dark:text-amber-300/80">
                        This sale was synced from an offline register. One or more item prices differ from the current
                        catalog price.
                        The customer was charged the price shown on their device at the time of sale.
                    </p>
                </div>
            </div>
        @endif

        {{-- Sale Summary Card --}}
        <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-primary-green-light text-primary-green flex items-center justify-center">
                            <x-icon name="receipt" class="w-6 h-6" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-primary font-mono-num">{{ $sale->invoice_number }}</h2>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-secondary mt-0.5">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="calendar" class="w-3.5 h-3.5" />
                                    {{ $sale->created_at->format('M d, Y \a\t g:i A') }}
                                </span>
                                @if ($sale->was_created_offline)
                                    <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                    <span class="flex items-center gap-1.5 text-amber-600">
                                        <x-icon name="exclamation" class="w-3.5 h-3.5" />
                                        Offline Sale
                                    </span>
                                @endif
                            </div>
                            @if ($sale->was_created_offline)
                                <p class="text-xs text-secondary opacity-60 mt-1">
                                    Recorded offline at
                                    {{ \Carbon\Carbon::parse($sale->created_offline_at)->format('g:i A') }},
                                    synced at {{ $sale->synced_at?->format('g:i A') }}
                                    via register {{ $sale->register->name ?? 'unknown' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $statusColors = [
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'refunded' => 'warning',
                            'partially_refunded' => 'warning',
                        ];
                    @endphp
                    <x-badge :color="$statusColors[$sale->status->value]" class="text-sm px-4 py-1.5">
                        <span class="flex items-center gap-1.5">
                            @if ($sale->status->value === 'completed')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            @endif
                            {{ $sale->status->label() }}
                        </span>
                    </x-badge>
                    @if ($sale->has_price_deviation)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 text-xs font-medium">
                            <x-icon name="alert-triangle" class="w-3.5 h-3.5" />
                            Price Deviation
                        </span>
                    @endif
                </div>
            </div>

            {{-- Details Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-theme">
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Customer</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-primary-green-light text-primary-green flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ $sale->customer->is_guest ? 'W' : substr($sale->customer->name, 0, 1) }}
                        </span>
                        {{ $sale->customer->is_guest ? 'Walk-in Customer' : $sale->customer->name }}
                    </p>
                </div>
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Cashier</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <x-icon name="user-check" class="w-4 h-4 text-secondary opacity-50" />
                        {{ $sale->cashier->name }}
                    </p>
                </div>
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Warehouse</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <x-icon name="warehouse" class="w-4 h-4 text-secondary opacity-50" />
                        {{ $sale->warehouse->name }}
                    </p>
                </div>
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Payment Method</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <x-icon name="credit-card" class="w-4 h-4 text-secondary opacity-50" />
                        {{ $sale->payments->pluck('method')->map(fn($m) => $m->label())->join(', ') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="px-6 py-4 border-b border-theme flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-xl bg-primary-green-light text-primary-green flex items-center justify-center">
                        <x-icon name="cube" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Order Items</h3>
                    <span class="text-xs text-secondary bg-primary-green-light/20 px-2.5 py-1 rounded-full">
                        {{ $sale->items->count() }} items
                    </span>
                </div>
                <span class="text-xs text-secondary">
                    <span class="font-medium text-primary">{{ $sale->items->sum('quantity') }}</span> total units
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-primary-green-light/20">
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
                                    Qty
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="cash" class="w-3.5 h-3.5" />
                                    Unit Price
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="refresh" class="w-3.5 h-3.5" />
                                    Refunded
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="receipt" class="w-3.5 h-3.5" />
                                    Total
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme">
                        @foreach ($sale->items as $item)
                            <tr class="hover:bg-primary-green-light/5 transition">
                                <td class="px-6 py-4">
                                    <div class="min-w-0">
                                        <p class="font-medium text-primary">{{ $item->product_name_snapshot }}</p>
                                        <div class="flex items-center gap-2 text-xs text-secondary mt-0.5">
                                            <span class="font-mono-num">{{ $item->product_sku_snapshot }}</span>
                                            <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                            {{-- <span>{{ $item->product->unit->symbol ?? '—' }}</span> --}}
                                        </div>
                                        @if ($item->hasPriceDeviation())
                                            <p
                                                class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1">
                                                <x-icon name="alert-triangle" class="w-3 h-3" />
                                                Catalog price now
                                                {{ \App\Support\Money::fromUnits($item->current_price_at_sync_cents)->formatted() }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num font-medium text-primary">
                                    {{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-right font-mono-num text-secondary">
                                    {{ $item->unitPrice()->formatted() }}</td>
                                <td class="px-6 py-4 text-right font-mono-num">
                                    @if ($item->refunded_quantity > 0)
                                        <span class="text-amber-600 font-medium">{{ $item->refunded_quantity }}</span>
                                    @else
                                        <span class="text-secondary opacity-40">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num font-semibold text-primary">
                                    {{ \App\Support\Money::fromUnits($item->total_cents)->formatted() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-primary-green-light/10">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-secondary font-medium">Subtotal</td>
                            <td class="px-6 py-3 text-right font-mono-num text-secondary">
                                {{ \App\Support\Money::fromUnits($sale->subtotal_cents)->formatted() }}
                            </td>
                        </tr>
                        @if ($sale->discount_cents > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-2 text-right text-secondary font-medium">Discount</td>
                                <td class="px-6 py-2 text-right font-mono-num text-red-600">
                                    -{{ \App\Support\Money::fromUnits($sale->discount_cents)->formatted() }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="px-6 py-2 text-right text-secondary font-medium">Tax</td>
                            <td class="px-6 py-2 text-right font-mono-num text-secondary">
                                {{ \App\Support\Money::fromUnits($sale->tax_cents)->formatted() }}
                            </td>
                        </tr>
                        <tr class="border-t border-theme">
                            <td colspan="4" class="px-6 py-3 text-right font-bold text-primary text-base">Total</td>
                            <td class="px-6 py-3 text-right font-mono-num font-bold text-primary text-base">
                                {{ $sale->total()->formatted() }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-2 text-right text-secondary font-medium">Paid</td>
                            <td class="px-6 py-2 text-right font-mono-num text-emerald-600 font-medium">
                                {{ \App\Support\Money::fromUnits($sale->paid_cents)->formatted() }}
                            </td>
                        </tr>
                        @if ($sale->change_cents > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-2 text-right text-secondary font-medium">Change</td>
                                <td class="px-6 py-2 text-right font-mono-num text-secondary">
                                    {{ \App\Support\Money::fromUnits($sale->change_cents)->formatted() }}
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Refund History --}}
        @if ($sale->refunds->isNotEmpty())
            <div
                class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="px-6 py-4 border-b border-theme flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 flex items-center justify-center">
                        <x-icon name="refresh" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Refund History</h3>
                    <span class="text-xs text-secondary bg-amber-100/50 dark:bg-amber-900/20 px-2.5 py-1 rounded-full">
                        {{ $sale->refunds->count() }} refunds
                    </span>
                </div>
                <div class="divide-y divide-theme">
                    @foreach ($sale->refunds as $refund)
                        <div class="px-6 py-4 hover:bg-primary-green-light/5 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-primary text-lg font-mono-num">
                                            {{ \App\Support\Money::fromUnits($refund->amount_cents)->formatted() }}
                                        </span>
                                        <x-badge color="warning" class="text-xs">
                                            {{ ucwords(str_replace('_', ' ', $refund->refund_method)) }}
                                        </x-badge>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-secondary mt-1">
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="user" class="w-3.5 h-3.5" />
                                            {{ $refund->processedBy->name }}
                                        </span>
                                        <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="clock" class="w-3.5 h-3.5" />
                                            {{ $refund->created_at->format('M d, Y g:i A') }}
                                        </span>
                                        @if ($refund->reason)
                                            <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                            <span class="flex items-center gap-1.5">
                                                <x-icon name="info" class="w-3.5 h-3.5" />
                                                {{ $refund->reason }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($refund->items->isNotEmpty())
                                    <span class="text-xs text-secondary bg-primary-green-light/10 px-3 py-1 rounded-full">
                                        {{ $refund->items->count() }} items refunded
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Cancel Modal --}}
    <x-modal id="cancel-sale" title="Cancel Sale" description="This action cannot be undone" icon="danger">
        <form method="POST" action="{{ route('admin.sales.cancel', $sale) }}">
            @csrf
            <div class="space-y-4">
                <div
                    class="flex items-start gap-4 p-4 bg-red-50/50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800/50">
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                        <x-icon name="alert-triangle" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            This will void the entire sale
                        </p>
                        <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                            All item quantities will be restored to stock. This action cannot be undone.
                        </p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-3 text-secondary opacity-40">
                            <x-icon name="info" class="w-4 h-4" />
                        </div>
                        <textarea name="reason" rows="2" required placeholder="Please provide a reason for cancelling this sale..."
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition resize-none"></textarea>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="cancel-sale"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    Keep Sale
                </button>
                <button type="submit"
                    class="rounded-xl bg-red-600 hover:bg-red-700 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="x" class="w-4 h-4" />
                    Cancel Sale
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Refund Modal --}}
    <x-modal id="refund-sale" title="Process Refund" description="Select items and quantities to refund" icon="refresh"
        maxWidth="lg">
        <form method="POST" action="{{ route('admin.sales.refund', $sale) }}">
            @csrf
            <p class="text-sm text-secondary mb-4">Enter the quantity to refund for each item.</p>

            <div class="space-y-3 max-h-64 overflow-y-auto pr-1 custom-scroll">
                @foreach ($sale->items as $item)
                    @php $refundable = $item->quantityRefundable(); @endphp
                    <div
                        class="flex items-center justify-between gap-4 p-3 rounded-xl bg-primary-green-light/5 border border-theme hover:border-primary-green/30 transition
                        @if ($refundable === 0) opacity-50 @endif">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-primary text-sm truncate">{{ $item->product_name_snapshot }}</p>
                            <div class="flex items-center gap-2 text-xs text-secondary">
                                <span class="font-mono-num">{{ $item->product_sku_snapshot }}</span>
                                <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                <span>Refundable: <span class="font-medium text-primary">{{ $refundable }}</span> of
                                    {{ $item->quantity }}</span>
                                <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                <span>{{ $item->unitPrice()->formatted() }} each</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="relative w-24">
                                <input type="number" min="0" max="{{ $refundable }}" value="0"
                                    name="quantities[{{ $item->id }}]" {{ $refundable === 0 ? 'disabled' : '' }}
                                    class="w-full rounded-xl border-theme px-3 py-2 bg-card text-sm font-mono-num text-right focus:ring-2 focus:ring-primary-green focus:border-transparent transition
                                    @if ($refundable === 0) opacity-50 cursor-not-allowed @endif">
                                <span
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-secondary opacity-60">max</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">Refund Method</label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="credit-card" class="w-4 h-4" />
                        </div>
                        <select name="refund_method"
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition appearance-none cursor-pointer">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="store_credit">Store Credit</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="info" class="w-4 h-4" />
                        </div>
                        <input type="text" name="reason" required placeholder="Reason for refund..."
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="refund-sale"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-xl bg-amber-600 hover:bg-amber-700 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="refresh" class="w-4 h-4" />
                    Process Refund
                </button>
            </div>
        </form>
    </x-modal>
@endsection

@push('styles')
    <style>
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.3);
            border-radius: 4px;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.5);
        }
    </style>
@endpush
