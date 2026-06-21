@extends('layouts.admin')

@section('page-title', $purchase->purchase_number)
@section('breadcrumb', 'Purchase Order Details')

@section('content')
    <div class="space-y-6">
        {{-- Header Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('admin.purchases.index') }}"
                class="text-sm text-secondary hover:text-primary-green transition flex items-center gap-1.5 group">
                <x-icon name="chevron-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Back to Purchases
            </a>
            <div class="flex flex-wrap gap-2">
                @can('update', $purchase)
                    @if ($purchase->status->value === 'draft')
                        <form method="POST" action="{{ route('admin.purchases.mark-ordered', $purchase) }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-sm font-medium px-4 py-2 text-white shadow-sm hover:shadow-md transition">
                                <x-icon name="check-circle" class="w-4 h-4" />
                                Mark as Ordered
                            </button>
                        </form>
                        {{-- <a href="{{ route('admin.purchases.edit', $purchase) }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                            <x-icon name="pencil" class="w-4 h-4" />
                            Edit
                        </a> --}}
                    @endif
                @endcan
                @can('cancel', $purchase)
                    @if (!in_array($purchase->status->value, ['received', 'cancelled']))
                        <button type="button" data-modal-target="cancel-purchase"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-300 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium px-4 py-2 transition">
                            <x-icon name="x" class="w-4 h-4" />
                            Cancel Order
                        </button>
                    @endif
                @endcan
                @can('receive', $purchase)
                    @if (in_array($purchase->status->value, ['ordered', 'partially_received']))
                        <button type="button" data-modal-target="receive-items"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-sm font-medium px-4 py-2 text-white shadow-sm hover:shadow-md transition">
                            <x-icon name="inbox" class="w-4 h-4" />
                            Receive Items
                        </button>
                    @endif
                @endcan
                {{-- @if ($purchase->status->value === 'received')
                    <a href="{{ route('admin.purchases.print', $purchase) }}" target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                        <x-icon name="printer" class="w-4 h-4" />
                        Print Order
                    </a>
                @endif --}}
            </div>
        </div>

        {{-- Purchase Summary Card --}}
        <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-primary-green-light text-primary-green flex items-center justify-center">
                            <x-icon name="clipboard-list" class="w-6 h-6" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-primary font-mono-num">{{ $purchase->purchase_number }}</h2>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-secondary mt-0.5">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="user" class="w-3.5 h-3.5" />
                                    Created by {{ $purchase->user->name }}
                                </span>
                                <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="calendar" class="w-3.5 h-3.5" />
                                    {{ $purchase->created_at->format('M d, Y \a\t g:i A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $statusColors = [
                            'draft' => 'gray',
                            'ordered' => 'blue',
                            'partially_received' => 'warning',
                            'received' => 'success',
                            'cancelled' => 'danger',
                        ];
                    @endphp
                    <x-badge :color="$statusColors[$purchase->status->value]" class="text-sm px-4 py-1.5">
                        <span class="flex items-center gap-1.5">
                            @if ($purchase->status->value === 'received')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            @elseif($purchase->status->value === 'ordered')
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            @elseif($purchase->status->value === 'partially_received')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            @endif
                            {{ $purchase->status->label() }}
                        </span>
                    </x-badge>
                    @if ($purchase->status->value === 'draft')
                        <span class="text-xs text-secondary bg-primary-green-light/20 px-2.5 py-1 rounded-full">
                            <x-icon name="pencil" class="w-3 h-3 inline mr-1" />
                            Editable
                        </span>
                    @endif
                </div>
            </div>

            {{-- Details Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-theme">
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Supplier</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-primary-green-light text-primary-green flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ substr($purchase->supplier->name, 0, 1) }}
                        </span>
                        {{ $purchase->supplier->name }}
                    </p>
                </div>
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Warehouse</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <x-icon name="warehouse" class="w-4 h-4 text-secondary opacity-50" />
                        {{ $purchase->warehouse->name }}
                    </p>
                </div>
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Order Date</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <x-icon name="calendar" class="w-4 h-4 text-secondary opacity-50" />
                        {{ $purchase->order_date->format('M d, Y') }}
                    </p>
                </div>
                <div class="bg-primary-green-light/5 rounded-xl p-3">
                    <p class="text-xs font-medium text-secondary uppercase tracking-wider">Expected Date</p>
                    <p class="font-medium text-primary mt-1 flex items-center gap-2">
                        <x-icon name="clock" class="w-4 h-4 text-secondary opacity-50" />
                        {{ $purchase->expected_date?->format('M d, Y') ?? '—' }}
                    </p>
                </div>
            </div>

            {{-- Notes --}}
            @if ($purchase->notes)
                <div class="mt-4 p-4 bg-primary-green-light/10 rounded-xl border border-theme">
                    <div class="flex items-start gap-2">
                        <x-icon name="info" class="w-4 h-4 text-secondary opacity-50 mt-0.5" />
                        <div>
                            <p class="text-xs font-medium text-secondary uppercase tracking-wider">Notes</p>
                            <p class="text-sm text-primary mt-1">{{ $purchase->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif
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
                        {{ $purchase->items->count() }} items
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-secondary">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $purchase->items->sum('quantity_received') }} received
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        {{ $purchase->items->sum('quantity_ordered') }} ordered
                    </span>
                </div>
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
                                    Ordered
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                    Received
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="cash" class="w-3.5 h-3.5" />
                                    Unit Cost
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="receipt" class="w-3.5 h-3.5" />
                                    Subtotal
                                </span>
                            </th>
                            <th
                                class="px-6 py-3.5 text-center font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-center gap-1.5">
                                    <x-icon name="settings" class="w-3.5 h-3.5" />
                                    Status
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme">
                        @foreach ($purchase->items as $item)
                            @php
                                $isFullyReceived = $item->quantity_received >= $item->quantity_ordered;
                                $isPartiallyReceived = $item->quantity_received > 0 && !$isFullyReceived;
                            @endphp
                            <tr class="hover:bg-primary-green-light/5 transition">
                                <td class="px-6 py-4">
                                    <div class="min-w-0">
                                        <p class="font-medium text-primary">{{ $item->product->name }}</p>
                                        <p class="text-xs text-secondary font-mono-num">{{ $item->product->sku }}</p>
                                        @if ($item->product->unit)
                                            <span
                                                class="text-xs text-secondary opacity-60">{{ $item->product->unit->symbol }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num font-medium text-primary">
                                    {{ $item->quantity_ordered }}</td>
                                <td class="px-6 py-4 text-right font-mono-num">
                                    @if ($isFullyReceived)
                                        <span class="text-emerald-600 font-medium flex items-center justify-end gap-1.5">
                                            <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                            {{ $item->quantity_received }}
                                        </span>
                                    @elseif($isPartiallyReceived)
                                        <span class="text-amber-600 font-medium flex items-center justify-end gap-1.5">
                                            <x-icon name="alert-triangle" class="w-3.5 h-3.5" />
                                            {{ $item->quantity_received }}
                                        </span>
                                    @else
                                        <span class="text-secondary opacity-40">{{ $item->quantity_received }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num text-secondary">
                                    {{ $item->unitCost()->formatted() }}</td>
                                <td class="px-6 py-4 text-right font-mono-num font-semibold text-primary">
                                    {{ \App\Support\Money::fromAmount($item->subtotal_cents)->formatted() }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($isFullyReceived)
                                        <x-badge color="success" class="text-[10px]">Fully Received</x-badge>
                                    @elseif($isPartiallyReceived)
                                        <x-badge color="warning" class="text-[10px]">Partially Received</x-badge>
                                    @else
                                        <x-badge color="gray" class="text-[10px]">Pending</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-primary-green-light/10">
                        <tr>
                            <td colspan="5" class="px-6 py-3 text-right text-secondary font-medium">Subtotal</td>
                            <td class="px-6 py-3 text-right font-mono-num text-secondary">
                                {{ \App\Support\Money::fromAmount($purchase->subtotal_cents)->formatted() }}
                            </td>
                        </tr>
                        @if ($purchase->discount_cents > 0)
                            <tr>
                                <td colspan="5" class="px-6 py-2 text-right text-secondary font-medium">Discount</td>
                                <td class="px-6 py-2 text-right font-mono-num text-red-600">
                                    -{{ \App\Support\Money::fromAmount($purchase->discount_cents)->formatted() }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="5" class="px-6 py-2 text-right text-secondary font-medium">Tax</td>
                            <td class="px-6 py-2 text-right font-mono-num text-secondary">
                                {{ \App\Support\Money::fromAmount($purchase->tax_cents)->formatted() }}
                            </td>
                        </tr>
                        <tr class="border-t border-theme">
                            <td colspan="5" class="px-6 py-3 text-right font-bold text-primary text-base">Total</td>
                            <td class="px-6 py-3 text-right font-mono-num font-bold text-primary text-base">
                                {{ $purchase->total()->formatted() }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Receiving History (if partially received) --}}
        {{-- @if ($purchase->receipts->isNotEmpty())
            <div
                class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="px-6 py-4 border-b border-theme flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 flex items-center justify-center">
                        <x-icon name="inbox" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Receiving History</h3>
                    <span class="text-xs text-secondary bg-emerald-100/50 dark:bg-emerald-900/20 px-2.5 py-1 rounded-full">
                        {{ $purchase->receipts->count() }} receipts
                    </span>
                </div>
                <div class="divide-y divide-theme">
                    @foreach ($purchase->receipts as $receipt)
                        <div class="px-6 py-4 hover:bg-primary-green-light/5 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-primary">
                                            {{ \App\Support\Money::fromAmount($receipt->items->sum('subtotal_cents'))->formatted() }}
                                        </span>
                                        <x-badge color="success" class="text-xs">Received</x-badge>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-secondary mt-1">
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="user" class="w-3.5 h-3.5" />
                                            {{ $receipt->user->name }}
                                        </span>
                                        <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="clock" class="w-3.5 h-3.5" />
                                            {{ $receipt->created_at->format('M d, Y g:i A') }}
                                        </span>
                                        <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="cube" class="w-3.5 h-3.5" />
                                            {{ $receipt->items->sum('quantity_received') }} units
                                        </span>
                                    </div>
                                </div>
                                @if ($receipt->notes)
                                    <span class="text-xs text-secondary bg-primary-green-light/10 px-3 py-1 rounded-full">
                                        {{ $receipt->notes }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif --}}

        {{-- Related Actions --}}
        <div class="flex flex-wrap gap-3 justify-end">
            {{-- @can('cancel', $purchase)
                @if (!in_array($purchase->status->value, ['received', 'cancelled']))
                    <button type="button" data-modal-target="cancel-purchase"
                        class="inline-flex items-center gap-2 rounded-xl border border-red-300 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium px-5 py-2.5 transition">
                        <x-icon name="x" class="w-4 h-4" />
                        Cancel Order
                    </button>
                @endif
            @endcan
            @can('receive', $purchase)
                @if (in_array($purchase->status->value, ['ordered', 'partially_received']))
                    <button type="button" data-modal-target="receive-items"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-sm font-medium px-5 py-2.5 text-white shadow-sm hover:shadow-md transition">
                        <x-icon name="inbox" class="w-4 h-4" />
                        Receive Items
                    </button>
                @endif
            @endcan --}}
            {{-- @if ($purchase->status->value === 'received')
                <a href="{{ route('admin.purchases.print', $purchase) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-5 py-2.5 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    <x-icon name="printer" class="w-4 h-4" />
                    Print Order
                </a>
            @endif --}}
        </div>
    </div>

    {{-- Receive Items Modal --}}
    <x-modal id="receive-items" title="Receive Items" description="Enter the quantity received for each item"
        icon="inbox" maxWidth="lg">
        <form method="POST" action="{{ route('admin.purchases.receive', $purchase) }}">
            @csrf
            <p class="text-sm text-secondary mb-4">Enter the quantity received for each item. Leave blank or 0 to skip a
                line.</p>

            <div class="space-y-3 max-h-64 overflow-y-auto pr-1 custom-scroll">
                @foreach ($purchase->items as $item)
                    @php $outstanding = $item->quantityOutstanding(); @endphp
                    <div
                        class="flex items-center justify-between gap-4 p-3 rounded-xl bg-primary-green-light/5 border border-theme hover:border-primary-green/30 transition
                        @if ($outstanding === 0) opacity-50 @endif">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-primary text-sm truncate">{{ $item->product->name }}</p>
                            <div class="flex items-center gap-2 text-xs text-secondary">
                                <span class="font-mono-num">{{ $item->product->sku }}</span>
                                <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                <span>Outstanding: <span
                                        class="font-medium text-primary">{{ $outstanding }}</span></span>
                                <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                                <span>Ordered: {{ $item->quantity_ordered }}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="relative w-24">
                                <input type="number" min="0" max="{{ $outstanding }}" value="0"
                                    name="received[{{ $item->id }}]" {{ $outstanding === 0 ? 'disabled' : '' }}
                                    class="w-full rounded-xl border-theme px-3 py-2 bg-card text-sm font-mono-num text-right focus:ring-2 focus:ring-primary-green focus:border-transparent transition
                                    @if ($outstanding === 0) opacity-50 cursor-not-allowed @endif">
                                <span
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-secondary opacity-60">max</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-secondary mb-1.5">Notes (Optional)</label>
                <div class="relative">
                    <div class="absolute left-3 top-3 text-secondary opacity-40">
                        <x-icon name="info" class="w-4 h-4" />
                    </div>
                    <textarea name="receipt_notes" rows="2" placeholder="Add any notes about this receipt..."
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition resize-none"></textarea>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="receive-items"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="check-circle" class="w-4 h-4" />
                    Confirm Receipt
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Cancel Purchase Modal --}}
    <x-modal id="cancel-purchase" title="Cancel Purchase Order" description="This action cannot be undone"
        icon="danger">
        <form method="POST" action="{{ route('admin.purchases.cancel', $purchase) }}">
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
                            Cancel <strong
                                class="text-red-900 dark:text-red-100">{{ $purchase->purchase_number }}</strong>?
                        </p>
                        <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                            This will cancel the entire purchase order. This action cannot be undone.
                        </p>
                        @if ($purchase->items->sum('quantity_received') > 0)
                            <p class="text-xs text-amber-600/70 dark:text-amber-300/70 mt-1">
                                <x-icon name="alert-triangle" class="w-3 h-3 inline" />
                                {{ $purchase->items->sum('quantity_received') }} items have already been received.
                            </p>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Reason (Optional)
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-3 text-secondary opacity-40">
                            <x-icon name="info" class="w-4 h-4" />
                        </div>
                        <textarea name="cancel_reason" rows="2" placeholder="Please provide a reason for cancelling..."
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition resize-none"></textarea>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="cancel-purchase"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    Keep Order
                </button>
                <button type="submit"
                    class="rounded-xl bg-red-600 hover:bg-red-700 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="x" class="w-4 h-4" />
                    Cancel Order
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
