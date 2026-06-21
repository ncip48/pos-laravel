@extends('layouts.admin')

@section('page-title', 'Categories')
@section('breadcrumb', 'Product Organization')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="tag" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Categories</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $categories->count() }} categories total</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ $categories->where('is_active', true)->count() }} active
                        </span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span>{{ $categories->where('parent_id', null)->count() }} root categories</span>
                    </div>
                </div>
            </div>
            <button type="button" data-modal-target="create-category"
                class="inline-flex items-center gap-2 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200 group">
                <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                Add Category
            </button>
        </div>

        {{-- Categories List --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            @forelse ($categories as $category)
                <div class="px-6 py-4 border-b border-theme last:border-0 hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-8 h-8 rounded-xl bg-sage-100/50 dark:bg-sage-800/30 flex items-center justify-center flex-shrink-0">
                                <x-icon name="folder" class="w-4 h-4 text-sage-600 dark:text-sage-400" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-primary">{{ $category->name }}</span>
                                    @if (!$category->is_active)
                                        <x-badge color="gray">Inactive</x-badge>
                                    @endif
                                    @if ($category->children->isNotEmpty())
                                        <x-badge color="sage" class="text-xs">
                                            {{ $category->children->count() }} subcategories
                                        </x-badge>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-xs text-secondary mt-0.5">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="cube" class="w-3 h-3" />
                                        {{ $category->products_count ?? $category->products()->count() }} products
                                    </span>
                                    @if ($category->description)
                                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                                        <span class="truncate max-w-[200px]">{{ $category->description }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-1 flex-shrink-0 ml-4">
                            <button type="button" data-modal-target="edit-category" data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}" data-parent="{{ $category->parent_id }}"
                                data-description="{{ $category->description }}" data-active="{{ $category->is_active }}"
                                class="p-1.5 rounded-lg text-secondary hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 transition">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </button>
                            <button type="button" data-modal-target="delete-category-{{ $category->id }}"
                                class="p-1.5 rounded-lg text-secondary hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    {{-- Subcategories --}}
                    @if ($category->children->isNotEmpty())
                        <div class="mt-3 ml-4 pl-4 border-l-2 border-theme space-y-1.5">
                            @foreach ($category->children as $child)
                                <div
                                    class="flex items-center justify-between text-sm py-1.5 px-2 rounded-lg hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition group">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <x-icon name="folder" class="w-3.5 h-3.5 text-secondary opacity-50" />
                                        <span
                                            class="text-secondary group-hover:text-primary transition truncate">{{ $child->name }}</span>
                                        @if (!$child->is_active)
                                            <x-badge color="gray" class="text-[10px]">Inactive</x-badge>
                                        @endif
                                        <span class="text-xs text-secondary opacity-60">
                                            ({{ $child->products_count ?? $child->products()->count() }} products)
                                        </span>
                                    </div>
                                    <div class="flex gap-0.5">
                                        <button type="button" data-modal-target="edit-category"
                                            data-id="{{ $child->id }}" data-name="{{ $child->name }}"
                                            data-parent="{{ $child->parent_id }}"
                                            data-description="{{ $child->description }}"
                                            data-active="{{ $child->is_active }}"
                                            class="p-1 rounded text-secondary opacity-0 group-hover:opacity-100 hover:text-sage-700 dark:hover:text-sage-300 transition">
                                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                                        </button>
                                        <button type="button" data-modal-target="delete-category-{{ $child->id }}"
                                            class="p-1 rounded text-secondary opacity-0 group-hover:opacity-100 hover:text-red-600 dark:hover:text-red-400 transition">
                                            <x-icon name="trash" class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Delete Modal --}}
                <x-modal id="delete-category-{{ $category->id }}" title="Delete Category"
                    description="This action cannot be undone" icon="danger">
                    <div class="space-y-3">
                        <div
                            class="flex items-start gap-4 p-4 bg-red-50/50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800/50">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                                <x-icon name="alert-triangle" class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Delete <strong class="text-red-900 dark:text-red-100">{{ $category->name }}</strong>?
                                </p>
                                @if ($category->children->isNotEmpty())
                                    <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                                        This category has {{ $category->children->count() }} subcategories that will also
                                        be deleted.
                                    </p>
                                @endif
                                @if ($category->products()->count() > 0)
                                    <p class="text-xs text-red-600/70 dark:text-red-300/70">
                                        {{ $category->products()->count() }} products will be uncategorized.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                            class="flex justify-end gap-2">
                            @csrf @method('DELETE')
                            <button type="button" data-modal-close="delete-category-{{ $category->id }}"
                                class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-800/30 hover:text-primary transition">
                                Cancel
                            </button>
                            <button type="submit"
                                class="rounded-xl bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                <x-icon name="trash" class="w-4 h-4" />
                                Delete Category
                            </button>
                        </form>
                    </div>
                </x-modal>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 rounded-2xl bg-sage-100/30 dark:bg-sage-800/20 flex items-center justify-center mb-4">
                            <x-icon name="folder" class="w-10 h-10 text-secondary opacity-30" />
                        </div>
                        <p class="text-lg font-medium text-primary">No categories yet</p>
                        <p class="text-sm text-secondary mt-1">Create your first category to start organizing products</p>
                        <button type="button" data-modal-target="create-category"
                            class="inline-flex items-center gap-2 mt-4 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200">
                            <x-icon name="plus" class="w-4 h-4" />
                            Create Category
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Edit Modal --}}
    <x-modal id="edit-category" title="Edit Category" description="Update category details" icon="pencil">
        <form method="POST" id="edit-category-form">
            @csrf
            @method('PUT')
            @include('admin.categories._fields', ['isEdit' => true])

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="edit-category"
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

    {{-- Create Modal --}}
    <x-modal id="create-category" title="Add Category" description="Create a new category to organize your products"
        icon="plus">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            @include('admin.categories._fields')

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="create-category"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-800/30 hover:text-primary transition">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4" />
                    Create Category
                </button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit category modal handler
            document.querySelectorAll('[data-modal-target="edit-category"]').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const form = document.getElementById('edit-category-form');

                    if (form) {
                        form.action = `/admin/categories/${id}`;
                    }

                    const nameInput = document.getElementById('edit-name');
                    if (nameInput) {
                        nameInput.value = this.dataset.name || '';
                    }

                    const parentSelect = document.getElementById('edit-parent');
                    if (parentSelect) {
                        parentSelect.value = this.dataset.parent || '';
                    }

                    const descriptionInput = document.getElementById('edit-description');
                    if (descriptionInput) {
                        descriptionInput.value = this.dataset.description || '';
                    }

                    const activeCheckbox = document.getElementById('edit-active');
                    if (activeCheckbox) {
                        activeCheckbox.checked = this.dataset.active == '1';
                    }
                });
            });

            // Auto-close modals on successful form submission (optional)
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const closeBtn = this.querySelector('[data-modal-close]');
                    if (closeBtn) {
                        setTimeout(() => {
                            closeBtn.click();
                        }, 100);
                    }
                });
            });
        });
    </script>
@endpush
