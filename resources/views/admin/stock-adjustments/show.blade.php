@extends('layouts.admin')

@section('page-title', $adjustment->adjustment_number)

@section('content')
    <div class="space-y-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.stock-adjustments.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr;
                Back to Adjustments</a>

            @can('stock-adjustments.approve')
                @if (!$adjustment->isApproved())
                    <button type="button" data-modal-target="approve-adjustment"
                        class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-sm font-medium px-4 py-2 text-white">
                        Approve & Apply to Stock
                    </button>
                @endif
            @endcan
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 font-mono-num">{{ $adjustment->adjustment_number }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ $adjustment->warehouse->name }} · {{ ucwords(str_replace('_', ' ', $adjustment->reason)) }}
                    </p>
                </div>
                <x-badge :color="$adjustment->isApproved() ? 'green' : 'amber'" class="text-sm px-3 py-1">
                    {{ $adjustment->isApproved() ? 'Approved' : 'Pending Approval' }}
                </x-badge>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-5 pt-5 border-t border-slate-100 text-sm">
                <div>
                    <p class="text-slate-500">Created By</p>
                    <p class="font-medium text-slate-900 mt-0.5">{{ $adjustment->user->name }} ·
                        {{ $adjustment->created_at->format('M d, Y g:i A') }}</p>
                </div>
                @if ($adjustment->isApproved())
                    <div>
                        <p class="text-slate-500">Approved By</p>
                        <p class="font-medium text-slate-900 mt-0.5">{{ $adjustment->approver->name }} ·
                            {{ $adjustment->approved_at->format('M d, Y g:i A') }}</p>
                    </div>
                @endif
            </div>

            @if ($adjustment->notes)
                <p class="mt-4 text-sm text-slate-600 bg-slate-50 rounded-lg p-3">{{ $adjustment->notes }}</p>
            @endif

            @if (!$adjustment->isApproved())
                <div
                    class="mt-4 flex items-start gap-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2.5 text-sm text-amber-800">
                    <x-icon name="exclamation" class="w-4 h-4 mt-0.5 shrink-0" />
                    <span>Stock levels have <strong>not</strong> been changed yet. Approve this adjustment to apply the
                        changes below.</span>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Product</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">System Qty</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Counted Qty</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Difference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($adjustment->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $item->product->name }}</p>
                                <p class="text-xs text-slate-400 font-mono-num">{{ $item->product->sku }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-mono-num text-slate-600">{{ $item->system_quantity }}</td>
                            <td class="px-4 py-3 text-right font-mono-num text-slate-900">{{ $item->counted_quantity }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-mono-num font-medium @if ($item->difference > 0) text-emerald-600 @elseif($item->difference < 0) text-red-600 @else text-slate-400 @endif">
                                {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <x-modal id="approve-adjustment" title="Approve Adjustment">
        <p class="text-sm text-slate-600">
            Approving <strong>{{ $adjustment->adjustment_number }}</strong> will immediately update stock levels
            for {{ $adjustment->items->count() }} product(s) in {{ $adjustment->warehouse->name }}. This cannot be undone.
        </p>
        <form method="POST" action="{{ route('admin.stock-adjustments.approve', $adjustment) }}"
            class="mt-4 flex justify-end gap-2">
            @csrf
            <button type="button" data-modal-close="approve-adjustment"
                class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
            <button type="submit"
                class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-sm font-medium px-4 py-2 text-white">Approve</button>
        </form>
    </x-modal>
@endsection
