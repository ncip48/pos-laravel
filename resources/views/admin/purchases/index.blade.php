@extends('layouts.admin')

@section('page-title', 'Purchases')
@section('breadcrumb', 'Inventory Procurement')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-green-light text-primary-green flex items-center justify-center">
                    <x-icon name="clipboard-list" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Purchase Orders</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $purchases->total() }} total orders</span>
                        <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $purchases->where('status', 'received')->count() }} received
                        </span>
                        <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ $purchases->whereIn('status', ['ordered', 'partially_received'])->count() }} pending
                        </span>
                    </div>
                </div>
            </div>
            @can('create', \App\Models\Purchase::class)
                <a href="{{ route('admin.purchases.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-green hover:bg-primary-green-dark px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200 group">
                    <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                    New Purchase
                </a>
            @endcan
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Total Orders</p>
                <p class="text-lg font-bold text-primary mt-1">{{ $purchases->total() }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Total Spent</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">
                    {{ \App\Support\Money::fromAmount($purchases->sum(fn($p) => $p->total()->amount()))->formatted() }}
                </p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Pending Orders</p>
                <p class="text-lg font-bold text-amber-600 mt-1">
                    {{ $purchases->whereIn('status', ['ordered', 'partially_received'])->count() }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Avg. Order Value</p>
                <p class="text-lg font-bold text-primary mt-1 font-mono-num">
                    {{ $purchases->count() ? \App\Support\Money::fromAmount($purchases->avg(fn($p) => $p->total()->amount()))->formatted() : '—' }}
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET"
            class="bg-card rounded-2xl border border-theme p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search PO number..."
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="check-circle" class="w-4 h-4" />
                    </div>
                    <select name="status"
                        class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition appearance-none cursor-pointer">
                        <option value="">All statuses</option>
                        @foreach (['draft', 'ordered', 'partially_received', 'received', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ ucwords(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="truck" class="w-4 h-4" />
                    </div>
                    <select name="supplier_id"
                        class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition appearance-none cursor-pointer">
                        <option value="">All suppliers</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? null) == $supplier->id)>{{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-green hover:bg-primary-green-dark text-white text-sm font-medium px-4 py-2.5 transition shadow-sm hover:shadow-md">
                        <x-icon name="filter" class="w-4 h-4" />
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status', 'supplier_id']))
                        <a href="{{ route('admin.purchases.index') }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                            <x-icon name="refresh" class="w-4 h-4" />
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            @if ($purchases->total() > 0)
                <div class="mt-3 pt-3 border-t border-theme flex items-center justify-between">
                    <span class="text-xs text-secondary">
                        <span class="font-medium text-primary">{{ $purchases->total() }}</span> orders found
                    </span>
                    <span class="text-xs text-secondary opacity-60">
                        Latest orders shown first
                    </span>
                </div>
            @endif
        </form>

        {{-- Purchases Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-primary-green-light/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="receipt" class="w-3.5 h-3.5" />
                                    PO Number
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="truck" class="w-3.5 h-3.5" />
                                    Supplier
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="warehouse" class="w-3.5 h-3.5" />
                                    Warehouse
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="calendar" class="w-3.5 h-3.5" />
                                    Order Date
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
                        @forelse ($purchases as $purchase)
                            @php
                                $statusColors = [
                                    'draft' => 'gray',
                                    'ordered' => 'blue',
                                    'partially_received' => 'warning',
                                    'received' => 'success',
                                    'cancelled' => 'danger',
                                ];
                                $statusIcons = [
                                    'draft' => 'pencil',
                                    'ordered' => 'clock',
                                    'partially_received' => 'alert-triangle',
                                    'received' => 'check-circle',
                                    'cancelled' => 'x',
                                ];
                            @endphp
                            <tr class="hover:bg-primary-green-light/5 transition group cursor-pointer"
                                onclick="window.location='{{ route('admin.purchases.show', $purchase) }}'">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-primary-green-light/20 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="clipboard-list" class="w-3.5 h-3.5 text-primary-green" />
                                        </div>
                                        <span
                                            class="font-mono-num font-semibold text-primary-green">{{ $purchase->purchase_number }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded-full bg-primary-green-light text-primary-green flex items-center justify-center text-xs font-medium flex-shrink-0">
                                            {{ substr($purchase->supplier->name, 0, 1) }}
                                        </div>
                                        <span class="text-secondary">{{ $purchase->supplier->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary-green-light/20 text-xs font-medium text-secondary">
                                        <x-icon name="warehouse" class="w-3 h-3" />
                                        {{ $purchase->warehouse->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-secondary text-sm">
                                        <x-icon name="calendar" class="w-3.5 h-3.5 text-secondary opacity-40" />
                                        {{ $purchase->order_date->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono-num font-semibold text-primary">
                                    {{ $purchase->total()->formatted() }}
                                    @if ($purchase->items->count() > 0)
                                        <div class="text-xs text-secondary font-normal">
                                            {{ $purchase->items->count() }} items
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$statusColors[$purchase->status->value]">
                                        <span class="flex items-center gap-1.5">
                                            @if ($purchase->status->value === 'received')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            @elseif($purchase->status->value === 'ordered')
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                            @endif
                                            {{ $purchase->status->label() }}
                                        </span>
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ route('admin.purchases.show', $purchase) }}"
                                            class="p-1.5 rounded-lg text-secondary hover:bg-primary-green-light hover:text-primary-green transition"
                                            title="View Details" onclick="event.stopPropagation();">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </a>
                                        {{-- @can('update', $purchase)
                                            @if ($purchase->status->value === 'draft')
                                                <a href="{{ route('admin.purchases.edit', $purchase) }}"
                                                    class="p-1.5 rounded-lg text-secondary hover:bg-primary-green-light hover:text-primary-green transition"
                                                    title="Edit" onclick="event.stopPropagation();">
                                                    <x-icon name="pencil" class="w-4 h-4" />
                                                </a>
                                            @endif
                                        @endcan --}}
                                        @if ($purchase->status->value === 'ordered' || $purchase->status->value === 'partially_received')
                                            <a href="{{ route('admin.purchases.receive', $purchase) }}"
                                                class="p-1.5 rounded-lg text-secondary hover:bg-emerald-100 hover:text-emerald-600 transition"
                                                title="Receive Items" onclick="event.stopPropagation();">
                                                <x-icon name="inbox" class="w-4 h-4" />
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-20 h-20 rounded-2xl bg-primary-green-light/20 flex items-center justify-center mb-4">
                                            <x-icon name="clipboard-list" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No purchase orders found</p>
                                        <p class="text-sm text-secondary mt-1">
                                            @if (request()->hasAny(['search', 'status', 'supplier_id']))
                                                Try adjusting your search filters
                                            @else
                                                Start by creating your first purchase order
                                            @endif
                                        </p>
                                        @can('create', \App\Models\Purchase::class)
                                            <a href="{{ route('admin.purchases.create') }}"
                                                class="inline-flex items-center gap-2 mt-4 rounded-xl bg-primary-green hover:bg-primary-green-dark px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200">
                                                <x-icon name="plus" class="w-4 h-4" />
                                                Create Purchase Order
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($purchases->hasPages())
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ $purchases->firstItem() ?? 0 }}</span>
                        to <span class="font-medium text-primary">{{ $purchases->lastItem() ?? 0 }}</span>
                        of <span class="font-medium text-primary">{{ $purchases->total() }}</span> orders
                    </div>
                    <div>
                        {{ $purchases->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
