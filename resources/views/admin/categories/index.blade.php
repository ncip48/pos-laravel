@extends('layouts.admin')

@section('page-title', 'Categories')

@section('content')
    <div class="space-y-5 max-w-4xl">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Categories</h2>
                <p class="text-sm text-slate-500 mt-0.5">Organize products into categories and subcategories.</p>
            </div>
            <button type="button" data-modal-target="create-category"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm">
                <x-icon name="plus" class="w-4 h-4" /> Add Category
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
            @forelse ($categories as $category)
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-slate-900">{{ $category->name }}</span>
                            @if (!$category->is_active)
                                <x-badge color="slate">Inactive</x-badge>
                            @endif
                            <span
                                class="text-xs text-slate-400">{{ $category->products_count ?? $category->products()->count() }}
                                products</span>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" data-modal-target="edit-category-{{ $category->id }}"
                                class="p-1.5 rounded-md text-slate-500 hover:bg-slate-100 hover:text-indigo-600">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </button>
                            <button type="button" data-modal-target="delete-category-{{ $category->id }}"
                                class="p-1.5 rounded-md text-slate-500 hover:bg-red-50 hover:text-red-600">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    @if ($category->children->isNotEmpty())
                        <div class="mt-2 ml-4 pl-3 border-l-2 border-slate-100 space-y-1">
                            @foreach ($category->children as $child)
                                <div class="flex items-center justify-between text-sm py-1">
                                    <span class="text-slate-600">{{ $child->name }}</span>
                                    <div class="flex gap-1">
                                        <button type="button" data-modal-target="edit-category-{{ $child->id }}"
                                            class="p-1 rounded text-slate-400 hover:text-indigo-600">
                                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Edit modal --}}
                <x-modal id="edit-category-{{ $category->id }}" title="Edit Category">
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                        @csrf @method('PUT')
                        @include('admin.categories._fields', ['category' => $category])
                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" data-modal-close="edit-category-{{ $category->id }}"
                                class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </x-modal>

                <x-modal id="delete-category-{{ $category->id }}" title="Delete Category">
                    <p class="text-sm text-slate-600">Delete <strong>{{ $category->name }}</strong>? This cannot be undone.
                    </p>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                        class="mt-4 flex justify-end gap-2">
                        @csrf @method('DELETE')
                        <button type="button" data-modal-close="delete-category-{{ $category->id }}"
                            class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit"
                            class="rounded-lg bg-red-600 hover:bg-red-500 text-sm font-medium px-4 py-2 text-white">Delete</button>
                    </form>
                </x-modal>
            @empty
                <div class="px-5 py-12 text-center text-slate-500">
                    <p class="font-medium">No categories yet</p>
                    <p class="text-sm mt-1">Create your first category to start organizing products.</p>
                </div>
            @endforelse
        </div>
    </div>

    <x-modal id="create-category" title="Add Category">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            @include('admin.categories._fields', ['categories' => $categories])
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="create-category"
                    class="rounded-lg border border-slate-300 text-sm font-medium px-4 py-2 text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-4 py-2 text-white">Create</button>
            </div>
        </form>
    </x-modal>
@endsection
