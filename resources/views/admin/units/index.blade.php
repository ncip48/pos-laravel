@extends('layouts.admin')

@section('page-title', 'Units')
@section('breadcrumb', 'Product Measurements')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-green-light text-primary-green flex items-center justify-center">
                    <x-icon name="scale" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Units of Measure</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $units->count() }} units defined</span>
                        <span class="w-1 h-1 rounded-full bg-secondary opacity-30"></span>
                        <span>Define how products are counted and sold</span>
                    </div>
                </div>
            </div>
            <button type="button" data-modal-target="create-unit"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-green hover:bg-primary-green-dark px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200 group">
                <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                Add Unit
            </button>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Total Units</p>
                <p class="text-lg font-bold text-primary mt-1">{{ $units->count() }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Products Using Units</p>
                <p class="text-lg font-bold text-primary mt-1">{{ $units->sum(fn($u) => $u->products()->count()) }}</p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Most Used Unit</p>
                <p class="text-lg font-bold text-primary mt-1 truncate">
                    {{ $units->sortByDesc(fn($u) => $u->products()->count())->first()?->symbol ?? '—' }}
                </p>
            </div>
            <div class="bg-card rounded-xl border border-theme p-4 shadow-sm">
                <p class="text-xs font-medium text-secondary uppercase tracking-wider">Active Units</p>
                <p class="text-lg font-bold text-primary mt-1">
                    {{ $units->filter(fn($u) => $u->is_active ?? true)->count() }}</p>
            </div>
        </div>

        {{-- Units Grid/List --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-primary-green-light/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="scale" class="w-3.5 h-3.5" />
                                    Unit Name
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="barcode" class="w-3.5 h-3.5" />
                                    Symbol
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="cube" class="w-3.5 h-3.5" />
                                    Products
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="inbox" class="w-3.5 h-3.5" />
                                    Total Stock
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="clock" class="w-3.5 h-3.5" />
                                    Added
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
                        @forelse ($units as $unit)
                            @php $productCount = $unit->products()->count(); @endphp
                            <tr class="hover:bg-primary-green-light/5 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-primary-green-light/30 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                            <x-icon name="scale" class="w-4 h-4 text-primary-green" />
                                        </div>
                                        <span class="font-medium text-primary">{{ $unit->name }}</span>
                                        @if (!($unit->is_active ?? true))
                                            <x-badge color="gray" class="text-[10px]">Inactive</x-badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-primary-green-light/20 font-mono-num font-semibold text-primary text-sm">
                                        {{ $unit->symbol }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-secondary">
                                    <span class="flex items-center gap-1.5">
                                        {{ $productCount }}
                                        @if ($productCount > 0)
                                            <span class="text-xs text-secondary opacity-60">products</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-secondary font-mono-num">
                                    @php $totalStock = $unit->products->sum(fn($p) => $p->totalStock()); @endphp
                                    {{ $totalStock > 0 ? number_format($totalStock) : '—' }}
                                </td>
                                <td class="px-6 py-4 text-secondary text-xs">
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="clock" class="w-3 h-3 text-secondary opacity-40" />
                                        {{ $unit->created_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @if ($productCount == 0)
                                            <button type="button" data-modal-target="edit-unit-{{ $unit->id }}"
                                                class="p-1.5 rounded-lg text-secondary hover:bg-primary-green-light hover:text-primary-green transition"
                                                title="Edit">
                                                <x-icon name="pencil" class="w-4 h-4" />
                                            </button>
                                        @else
                                            <span class="p-1.5 text-secondary opacity-30 cursor-not-allowed"
                                                title="Unit is in use and cannot be edited">
                                                <x-icon name="pencil" class="w-4 h-4" />
                                            </span>
                                        @endif

                                        @if ($productCount == 0)
                                            <button type="button" data-modal-target="delete-unit-{{ $unit->id }}"
                                                class="p-1.5 rounded-lg text-secondary hover:bg-red-50 hover:text-red-600 transition"
                                                title="Delete">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        @else
                                            <span class="p-1.5 text-secondary opacity-30 cursor-not-allowed"
                                                title="Unit is in use and cannot be deleted">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Edit Modal --}}
                            <x-modal id="edit-unit-{{ $unit->id }}" title="Edit Unit" description="Update unit details"
                                icon="pencil">
                                <form method="POST" action="{{ route('admin.units.update', $unit) }}">
                                    @csrf @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-secondary mb-1.5">
                                                Unit Name <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                                    <x-icon name="scale" class="w-4 h-4" />
                                                </div>
                                                <input type="text" name="name" value="{{ $unit->name }}"
                                                    required placeholder="e.g. Piece, Kilogram"
                                                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-secondary mb-1.5">
                                                Symbol <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                                    <x-icon name="barcode" class="w-4 h-4" />
                                                </div>
                                                <input type="text" name="symbol" value="{{ $unit->symbol }}"
                                                    required placeholder="e.g. pcs, kg"
                                                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm font-mono-num focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                                            </div>
                                        </div>
                                        <input type="hidden" name="conversion_factor"
                                            value="{{ $unit->conversion_factor }}">
                                    </div>
                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" data-modal-close="edit-unit-{{ $unit->id }}"
                                            class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="rounded-xl bg-primary-green hover:bg-primary-green-dark text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                            <x-icon name="check" class="w-4 h-4" />
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </x-modal>

                            {{-- Delete Modal --}}
                            <x-modal id="delete-unit-{{ $unit->id }}" title="Delete Unit"
                                description="This action cannot be undone" icon="danger">
                                <div class="space-y-3">
                                    @if ($productCount > 0)
                                        <div
                                            class="flex items-start gap-4 p-4 bg-amber-50/50 dark:bg-amber-900/10 rounded-xl border border-amber-200 dark:border-amber-800/50">
                                            <div
                                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                                <x-icon name="alert-triangle" class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                                    This unit is currently in use
                                                </p>
                                                <p class="text-xs text-amber-600/70 dark:text-amber-300/70 mt-1">
                                                    {{ $productCount }} products are using this unit. Delete it from
                                                    products first.
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
                                                        class="text-red-900 dark:text-red-100">{{ $unit->name }}</strong>?
                                                </p>
                                                <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                                                    This unit is not used by any products and can be safely removed.
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('admin.units.destroy', $unit) }}"
                                        class="flex justify-end gap-2">
                                        @csrf @method('DELETE')
                                        <button type="button" data-modal-close="delete-unit-{{ $unit->id }}"
                                            class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="rounded-xl bg-red-600 hover:bg-red-700 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2"
                                            @if ($productCount > 0) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                            <x-icon name="trash" class="w-4 h-4" />
                                            Delete Unit
                                        </button>
                                    </form>
                                </div>
                            </x-modal>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-20 h-20 rounded-2xl bg-primary-green-light/20 flex items-center justify-center mb-4">
                                            <x-icon name="scale" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No units defined</p>
                                        <p class="text-sm text-secondary mt-1">Create your first unit of measure</p>
                                        <button type="button" data-modal-target="create-unit"
                                            class="inline-flex items-center gap-2 mt-4 rounded-xl bg-primary-green hover:bg-primary-green-dark px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200">
                                            <x-icon name="plus" class="w-4 h-4" />
                                            Create Unit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <x-modal id="create-unit" title="Add Unit" description="Create a new unit of measure" icon="plus">
        <form method="POST" action="{{ route('admin.units.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Unit Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="scale" class="w-4 h-4" />
                        </div>
                        <input type="text" name="name" placeholder="e.g. Piece, Kilogram, Box" required
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Symbol <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="barcode" class="w-4 h-4" />
                        </div>
                        <input type="text" name="symbol" placeholder="e.g. pcs, kg, box" required
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm font-mono-num focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
                    </div>
                </div>
                <input type="hidden" name="conversion_factor" value="1">
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="create-unit"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-primary-green-light hover:text-primary transition">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-xl bg-primary-green hover:bg-primary-green-dark text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4" />
                    Create Unit
                </button>
            </div>
        </form>
    </x-modal>
@endsection
