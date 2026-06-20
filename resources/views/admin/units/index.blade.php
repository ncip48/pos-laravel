@extends('layouts.admin')

@section('page-title', 'Units')

@section('content')
    <div class="space-y-5 max-w-3xl">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Units of Measure</h2>
                <p class="text-sm text-slate-500 mt-0.5">Define how products are counted and sold (pieces, kilograms,
                    boxes...).</p>
            </div>
            <button type="button" data-modal-target="create-unit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm">
                <x-icon name="plus" class="w-4 h-4" /> Add Unit
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Symbol</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($units as $unit)
                        <tr>
                            <td class="px-4 py-3 text-slate-900">{{ $unit->name }}</td>
                            <td class="px-4 py-3 font-mono-num text-slate-600">{{ $unit->symbol }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" data-modal-target="edit-unit-{{ $unit->id }}"
                                        class="p-1.5 rounded-md text-slate-500 hover:bg-slate-100 hover:text-indigo-600">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </button>
                                    <button type="button" data-modal-target="delete-unit-{{ $unit->id }}"
                                        class="p-1.5 rounded-md text-slate-500 hover:bg-red-50 hover:text-red-600">
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <x-modal id="edit-unit-{{ $unit->id }}" title="Edit Unit">
                            <form method="POST" action="{{ route('admin.units.update', $unit) }}">
                                @csrf @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                                        <input type="text" name="name" value="{{ $unit->name }}" required
                                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Symbol</label>
                                        <input type="text" name="symbol" value="{{ $unit->symbol }}" required
                                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <input type="hidden" name="conversion_factor" value="{{ $unit->conversion_factor }}">
                                </div>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" data-modal-close="edit-unit-{{ $unit->id }}"
                                        class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
                                    <button type="submit"
                                        class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-4 py-2 text-white">Save</button>
                                </div>
                            </form>
                        </x-modal>

                        <x-modal id="delete-unit-{{ $unit->id }}" title="Delete Unit">
                            <p class="text-sm text-slate-600">Delete <strong>{{ $unit->name }}</strong>?</p>
                            <form method="POST" action="{{ route('admin.units.destroy', $unit) }}"
                                class="mt-4 flex justify-end gap-2">
                                @csrf @method('DELETE')
                                <button type="button" data-modal-close="delete-unit-{{ $unit->id }}"
                                    class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
                                <button type="submit"
                                    class="rounded-lg bg-red-600 hover:bg-red-500 text-sm font-medium px-4 py-2 text-white">Delete</button>
                            </form>
                        </x-modal>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center text-slate-500">No units defined yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal id="create-unit" title="Add Unit">
        <form method="POST" action="{{ route('admin.units.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                    <input type="text" name="name" placeholder="e.g. Piece, Kilogram" required
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Symbol</label>
                    <input type="text" name="symbol" placeholder="e.g. pcs, kg" required
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <input type="hidden" name="conversion_factor" value="1">
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="create-unit"
                    class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-4 py-2 text-white">Create</button>
            </div>
        </form>
    </x-modal>
@endsection
