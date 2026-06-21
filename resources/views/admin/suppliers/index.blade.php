@extends('layouts.admin')

@section('page-title', 'Suppliers')
@section('breadcrumb', 'Vendor Management')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="truck" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Suppliers</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $suppliers->total() }} suppliers total</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ $suppliers->where('is_active', true)->count() }} active
                        </span>
                    </div>
                </div>
            </div>
            @can('suppliers.create')
                <button type="button" data-modal-target="create-supplier"
                    class="inline-flex items-center gap-2 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200 group">
                    <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                    Add Supplier
                </button>
            @endcan
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Total Suppliers</p>
                <p class="text-lg font-bold text-primary mt-1">{{ $suppliers->total() }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Active</p>
                <p class="text-lg font-bold text-sage-600 dark:text-sage-400 mt-1">
                    {{ $suppliers->where('is_active', true)->count() }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Inactive</p>
                <p class="text-lg font-bold text-red-600 dark:text-red-400 mt-1">
                    {{ $suppliers->where('is_active', false)->count() }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Total Purchases</p>
                <p class="text-lg font-bold text-primary mt-1">{{ $suppliers->sum(fn($s) => $s->purchases()->count()) }}</p>
            </div>
        </div>

        {{-- Search --}}
        <form method="GET"
            class="bg-card rounded-2xl border border-theme p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search by name, contact person, phone, or email..."
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-white text-sm font-medium px-5 py-2.5 transition shadow-sm hover:shadow-md">
                        <x-icon name="search" class="w-4 h-4" />
                        Search
                    </button>
                    @if (request()->has('search'))
                        <a href="{{ route('admin.suppliers.index') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-5 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                            <x-icon name="x" class="w-4 h-4" />
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Suppliers Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-sage-50 dark:bg-sage-900/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="truck" class="w-3.5 h-3.5" />
                                    Supplier
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="user" class="w-3.5 h-3.5" />
                                    Contact
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="mail" class="w-3.5 h-3.5" />
                                    Phone / Email
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="shopping-bag" class="w-3.5 h-3.5" />
                                    Purchases
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                    Status
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-right font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center justify-end gap-1.5">
                                    <x-icon name="settings" class="w-3.5 h-3.5" />
                                    Actions
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme">
                        @forelse ($suppliers as $supplier)
                            @php $purchaseCount = $supplier->purchases()->count(); @endphp
                            <tr class="hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-sage-100/50 dark:bg-sage-800/30 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                            <x-icon name="truck" class="w-4 h-4 text-sage-600 dark:text-sage-400" />
                                        </div>
                                        <div>
                                            <span class="font-medium text-primary">{{ $supplier->name }}</span>
                                            @if ($supplier->tax_id)
                                                <div class="text-xs text-secondary font-mono-num">Tax:
                                                    {{ $supplier->tax_id }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-secondary">
                                        <span class="font-medium">{{ $supplier->contact_person ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-secondary">
                                        @if ($supplier->phone)
                                            <div class="flex items-center gap-1.5 text-sm">
                                                <x-icon name="phone" class="w-3.5 h-3.5 text-secondary opacity-40" />
                                                {{ $supplier->phone }}
                                            </div>
                                        @endif
                                        @if ($supplier->email)
                                            <div class="flex items-center gap-1.5 text-xs text-secondary opacity-70 mt-0.5">
                                                <x-icon name="mail" class="w-3 h-3" />
                                                <a href="mailto:{{ $supplier->email }}"
                                                    class="hover:text-sage-600 dark:hover:text-sage-400 transition">
                                                    {{ $supplier->email }}
                                                </a>
                                            </div>
                                        @endif
                                        @if (!$supplier->phone && !$supplier->email)
                                            <span class="text-secondary opacity-40">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($purchaseCount > 0)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sage-100/50 dark:bg-sage-800/30 text-sm font-medium text-sage-700 dark:text-sage-300 border border-sage-200 dark:border-sage-700">
                                            <x-icon name="shopping-bag" class="w-3.5 h-3.5" />
                                            {{ $purchaseCount }}
                                        </span>
                                    @else
                                        <span class="text-secondary opacity-40 text-xs">No purchases</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$supplier->is_active ? 'success' : 'gray'">
                                        <span class="flex items-center gap-1.5">
                                            @if ($supplier->is_active)
                                                <span class="w-1.5 h-1.5 rounded-full bg-sage-500 dark:bg-sage-400 animate-pulse"></span>
                                            @endif
                                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @can('suppliers.update')
                                            <button type="button" data-modal-target="edit-supplier-{{ $supplier->id }}"
                                                class="p-1.5 rounded-lg text-secondary hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 transition"
                                                title="Edit">
                                                <x-icon name="pencil" class="w-4 h-4" />
                                            </button>
                                        @endcan
                                        @if ($purchaseCount == 0)
                                            @can('suppliers.delete')
                                                <button type="button"
                                                    data-modal-target="delete-supplier-{{ $supplier->id }}"
                                                    class="p-1.5 rounded-lg text-secondary hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition"
                                                    title="Delete">
                                                    <x-icon name="trash" class="w-4 h-4" />
                                                </button>
                                            @endcan
                                        @else
                                            <span class="p-1.5 text-secondary opacity-30 cursor-not-allowed"
                                                title="Supplier has purchase records and cannot be deleted">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Edit Modal --}}
                            <x-modal id="edit-supplier-{{ $supplier->id }}" title="Edit Supplier"
                                description="Update supplier information" icon="pencil" maxWidth="lg">
                                <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}">
                                    @csrf @method('PUT')
                                    @include('admin.suppliers._fields', ['supplier' => $supplier])
                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" data-modal-close="edit-supplier-{{ $supplier->id }}"
                                            class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-800/30 hover:text-primary transition">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                            <x-icon name="check" class="w-4 h-4" />
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </x-modal>

                            {{-- Delete Modal --}}
                            <x-modal id="delete-supplier-{{ $supplier->id }}" title="Delete Supplier"
                                description="This action cannot be undone" icon="danger">
                                <div class="space-y-3">
                                    @if ($purchaseCount > 0)
                                        <div
                                            class="flex items-start gap-4 p-4 bg-amber-50/50 dark:bg-amber-900/10 rounded-xl border border-amber-200 dark:border-amber-800/50">
                                            <div
                                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                                <x-icon name="alert-triangle" class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                                    This supplier has purchase records
                                                </p>
                                                <p class="text-xs text-amber-600/70 dark:text-amber-300/70 mt-1">
                                                    {{ $purchaseCount }} purchases are associated with this supplier.
                                                    Delete purchases first.
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            class="flex items-start gap-4 p-4 bg-red-50/50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800/50">
                                            <div
                                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                                                <x-icon name="alert-triangle" class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                                    Delete <strong
                                                        class="text-red-900 dark:text-red-100">{{ $supplier->name }}</strong>?
                                                </p>
                                                <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                                                    This supplier has no purchase records and can be safely removed.
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex justify-end gap-2 pt-2">
                                        <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}"
                                            class="flex justify-end gap-2">
                                            @csrf @method('DELETE')
                                            <button type="button" data-modal-close="delete-supplier-{{ $supplier->id }}"
                                                class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-800/30 hover:text-primary transition">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="rounded-xl bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2"
                                                @if ($purchaseCount > 0) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                                <x-icon name="trash" class="w-4 h-4" />
                                                Delete Supplier
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </x-modal>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-20 h-20 rounded-2xl bg-sage-100/30 dark:bg-sage-800/20 flex items-center justify-center mb-4">
                                            <x-icon name="truck" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No suppliers found</p>
                                        <p class="text-sm text-secondary mt-1">
                                            @if (request()->has('search'))
                                                Try adjusting your search filters
                                            @else
                                                Start by adding your first supplier
                                            @endif
                                        </p>
                                        @can('suppliers.create')
                                            <button type="button" data-modal-target="create-supplier"
                                                class="inline-flex items-center gap-2 mt-4 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200">
                                                <x-icon name="plus" class="w-4 h-4" />
                                                Add Supplier
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($suppliers->hasPages())
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ $suppliers->firstItem() ?? 0 }}</span>
                        to <span class="font-medium text-primary">{{ $suppliers->lastItem() ?? 0 }}</span>
                        of <span class="font-medium text-primary">{{ $suppliers->total() }}</span> suppliers
                    </div>
                    <div>
                        {{ $suppliers->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Create Modal --}}
    <x-modal id="create-supplier" title="Add Supplier" description="Create a new supplier or vendor" icon="plus"
        maxWidth="lg">
        <form method="POST" action="{{ route('admin.suppliers.store') }}">
            @csrf
            @include('admin.suppliers._fields')
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="create-supplier"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-800/30 hover:text-primary transition">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4" />
                    Create Supplier
                </button>
            </div>
        </form>
    </x-modal>
@endsection
