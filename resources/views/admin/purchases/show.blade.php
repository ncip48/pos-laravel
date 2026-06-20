@extends('layouts.admin')

@section('page-title', $purchase->purchase_number)

@section('content')
    <div class="space-y-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.purchases.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back to
                Purchases</a>

            <div class="flex gap-2">
                @can('update', $purchase)
                    @if ($purchase->status->value === 'draft')
                        <form method="POST" action="{{ route('admin.purchases.mark-ordered', $purchase) }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-4 py-2 text-white">Mark
                                as Ordered</button>
                        </form>
                    @endif
                @endcan
                @can('cancel', $purchase)
                    @if (!in_array($purchase->status->value, ['received', 'cancelled']))
                        <button type="button" data-modal-target="cancel-purchase"
                            class="rounded-lg border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2">Cancel
                            Order</button>
                    @endif
                @endcan
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 font-mono-num">{{ $purchase->purchase_number }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Created
                        {{ $purchase->created_at->format('M d, Y \a\t g:i A') }} by {{ $purchase->user->name }}</p>
                </div>
                @php
                    $statusColors = [
                        'draft' => 'slate',
                        'ordered' => 'indigo',
                        'partially_received' => 'amber',
                        'received' => 'green',
                        'cancelled' => 'red',
                    ];
                @endphp
                <x-badge :color="$statusColors[$purchase->status->value]" class="text-sm px-3 py-1">{{ $purchase->status->label() }}</x-badge>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-5 border-t border-slate-100 text-sm">
                <div>
                    <p class="text-slate-500">Supplier</p>
                    <p class="font-medium text-slate-900 mt-0.5">{{ $purchase->supplier->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Warehouse</p>
                    <p class="font-medium text-slate-900 mt-0.5">{{ $purchase->warehouse->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Order Date</p>
                    <p class="font-medium text-slate-900 mt-0.5">{{ $purchase->order_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Expected Date</p>
                    <p class="font-medium text-slate-900 mt-0.5">{{ $purchase->expected_date?->format('M d, Y') ?? '—' }}
                    </p>
                </div>
            </div>
            @if ($purchase->notes)
                <p class="mt-4 text-sm text-slate-600 bg-slate-50 rounded-lg p-3">{{ $purchase->notes }}</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Items</h3>
                @can('receive', $purchase)
                    @if (in_array($purchase->status->value, ['ordered', 'partially_received']))
                        <button type="button" data-modal-target="receive-items"
                            class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-sm font-medium px-4 py-2 text-white">Receive
                            Items</button>
                    @endif
                @endcan
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Product</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Ordered</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Received</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Unit Cost</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $item->product->name }}</p>
                                <p class="text-xs text-slate-400 font-mono-num">{{ $item->product->sku }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-mono-num">{{ $item->quantity_ordered }}</td>
                            <td class="px-4 py-3 text-right font-mono-num">
                                <span
                                    class="@if ($item->quantity_received < $item->quantity_ordered) text-amber-600 @else text-emerald-600 @endif">
                                    {{ $item->quantity_received }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono-num">{{ $item->unitCost()->formatted() }}</td>
                            <td class="px-4 py-3 text-right font-mono-num font-medium">
                                {{ \App\Support\Money::fromAmount($item->subtotal_cents)->formatted() }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-slate-500">Subtotal</td>
                        <td class="px-4 py-2 text-right font-mono-num">
                            {{ \App\Support\Money::fromAmount($purchase->subtotal_cents)->formatted() }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-slate-500">Discount</td>
                        <td class="px-4 py-2 text-right font-mono-num">
                            -{{ \App\Support\Money::fromAmount($purchase->discount_cents)->formatted() }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-slate-500">Tax</td>
                        <td class="px-4 py-2 text-right font-mono-num">
                            {{ \App\Support\Money::fromAmount($purchase->tax_cents)->formatted() }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right font-semibold text-slate-900">Total</td>
                        <td class="px-4 py-3 text-right font-mono-num font-semibold text-slate-900">
                            {{ $purchase->total()->formatted() }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Receive items modal --}}
    <x-modal id="receive-items" title="Receive Items" maxWidth="lg">
        <form method="POST" action="{{ route('admin.purchases.receive', $purchase) }}">
            @csrf
            <p class="text-sm text-slate-500 mb-3">Enter the quantity received for each item. Leave blank or 0 to skip a
                line.</p>
            <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                @foreach ($purchase->items as $item)
                    @php $outstanding = $item->quantityOutstanding(); @endphp
                    <div
                        class="flex items-center justify-between gap-3 @if ($outstanding === 0) opacity-50 @endif">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 text-sm truncate">{{ $item->product->name }}</p>
                            <p class="text-xs text-slate-500">Outstanding: {{ $outstanding }}</p>
                        </div>
                        <input type="number" min="0" max="{{ $outstanding }}" value="0"
                            name="received[{{ $item->id }}]" {{ $outstanding === 0 ? 'disabled' : '' }}
                            class="w-24 rounded-lg border-slate-300 text-sm font-mono-num text-right">
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="receive-items"
                    class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit"
                    class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-sm font-medium px-4 py-2 text-white">Confirm
                    Receipt</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="cancel-purchase" title="Cancel Purchase Order">
        <p class="text-sm text-slate-600">Cancel <strong>{{ $purchase->purchase_number }}</strong>? This cannot be undone.
        </p>
        <form method="POST" action="{{ route('admin.purchases.cancel', $purchase) }}" class="mt-4 flex justify-end gap-2">
            @csrf
            <button type="button" data-modal-close="cancel-purchase"
                class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Keep
                Order</button>
            <button type="submit"
                class="rounded-lg bg-red-600 hover:bg-red-500 text-sm font-medium px-4 py-2 text-white">Cancel
                Order</button>
        </form>
    </x-modal>
@endsection
