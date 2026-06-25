@extends('layouts.admin')

@section('page-title', 'Roles & Permissions')
@section('breadcrumb', 'Access Control')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="shield-check" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Roles & Permissions</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $roles->count() }} roles configured</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ $roles->sum('users_count') }} users assigned
                        </span>
                    </div>
                </div>
            </div>
            <button type="button" data-modal-target="create-role"
                class="inline-flex items-center gap-2 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200 group">
                <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                Add Role
            </button>
        </div>

        {{-- Role Tabs --}}
        <div class="flex flex-wrap gap-2" id="role-tabs">
            @foreach ($roles as $role)
                <button type="button" data-role-tab="{{ $role->id }}"
                    class="role-tab-btn rounded-xl px-5 py-2.5 text-sm font-medium border transition-all duration-200
                    {{ $loop->first
                        ? 'bg-sage-600 text-white border-sage-600 dark:bg-sage-500 dark:border-sage-500 shadow-sm'
                        : 'bg-card text-secondary border-theme hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary' }}">
                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    <span class="ml-1.5 text-xs opacity-70">({{ $role->users_count }})</span>
                </button>
            @endforeach
        </div>

        {{-- Role Panels --}}
        @foreach ($roles as $role)
            <div data-role-panel="{{ $role->id }}"
                class="role-panel {{ $loop->first ? '' : 'hidden' }} bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
                {{-- Admin Notice --}}
                @if ($role->name === 'admin')
                    <div class="flex items-start gap-3 rounded-xl bg-sage-50/50 dark:bg-sage-900/20 border border-sage-200 dark:border-sage-700 px-4 py-3 text-sm text-sage-700 dark:text-sage-300 mb-5">
                        <x-icon name="shield-check" class="w-5 h-5 text-sage-600 dark:text-sage-400 mt-0.5 shrink-0" />
                        <span>The <strong>admin</strong> role always has full access to every permission and cannot be modified.</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-5">
                        @foreach ($permissions as $module => $modulePermissions)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-secondary mb-2.5">
                                    {{ str_replace('-', ' ', $module) }}</p>
                                <div class="space-y-2">
                                    @foreach ($modulePermissions as $permission)
                                        <label class="flex items-center gap-2.5 text-sm text-secondary cursor-pointer group">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                {{ $role->name === 'admin' ? 'checked disabled' : '' }}
                                                @checked($role->hasPermissionTo($permission))
                                                class="w-4 h-4 rounded border-theme text-sage-600 dark:text-sage-400 focus:ring-sage-400 dark:focus:ring-sage-500 focus:ring-2 transition
                                                {{ $role->name === 'admin' ? 'cursor-not-allowed opacity-60' : '' }}">
                                            <span class="group-hover:text-primary transition">
                                                {{ str_replace([$module . '.', '-'], ['', ' '], $permission->name) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($role->name !== 'admin')
                        <div class="mt-6 pt-5 border-t border-theme flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            @if (!in_array($role->name, ['admin', 'manager', 'cashier', 'stock_clerk']))
                                <button type="button" data-modal-target="delete-role-{{ $role->id }}"
                                    class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition flex items-center gap-1.5">
                                    <x-icon name="trash" class="w-4 h-4" />
                                    Delete Role
                                </button>
                            @else
                                <span class="text-xs text-secondary opacity-60">System role — cannot be deleted</span>
                            @endif
                            <button type="submit"
                                class="rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-6 py-2.5 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                <x-icon name="save" class="w-4 h-4" />
                                Save Permissions
                            </button>
                        </div>
                    @endif
                </form>
            </div>

            {{-- Delete Role Modal --}}
            @if (!in_array($role->name, ['admin', 'manager', 'cashier', 'stock_clerk']))
                <x-modal id="delete-role-{{ $role->id }}" title="Delete Role" icon="danger">
                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 bg-red-50/50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800/50">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                                <x-icon name="alert-triangle" class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Delete <strong class="text-red-900 dark:text-red-100">{{ ucwords(str_replace('_', ' ', $role->name)) }}</strong>?
                                </p>
                                <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                                    This will permanently delete the role and remove it from any assigned users.
                                    @if ($role->users_count > 0)
                                        <strong class="text-red-700 dark:text-red-300">{{ $role->users_count }}</strong> users are currently assigned to this role.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-medium text-secondary">
                                Type <span class="font-bold text-primary">DELETE</span> to confirm
                            </label>
                            <input type="text" id="confirm-delete-role-{{ $role->id }}"
                                class="w-full rounded-xl border-theme px-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                                placeholder="Type DELETE to confirm..." autocomplete="off">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" data-modal-close="delete-role-{{ $role->id }}"
                            class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                            Cancel
                        </button>
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" id="delete-role-form-{{ $role->id }}">
                            @csrf @method('DELETE')
                            <button type="submit" id="delete-role-confirm-{{ $role->id }}" disabled
                                class="rounded-xl bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                <x-icon name="trash" class="w-4 h-4" />
                                Delete Role
                            </button>
                        </form>
                    </div>
                </x-modal>
            @endif
        @endforeach
    </div>

    {{-- Create Role Modal --}}
    <x-modal id="create-role" title="Add Role" description="Create a new role and assign permissions later" icon="plus">
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="shield-check" class="w-4 h-4" />
                        </div>
                        <input type="text" name="name" placeholder="e.g. delivery_driver" pattern="[a-z_]+" required
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                    </div>
                    <p class="mt-1.5 text-xs text-secondary opacity-60">Lowercase letters and underscores only</p>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="create-role"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4" />
                    Create Role
                </button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Role tab switching
                $('.role-tab-btn').on('click', function() {
                    const roleId = $(this).data('role-tab');
                    $('.role-tab-btn').removeClass('bg-sage-600 text-white border-sage-600 dark:bg-sage-500 dark:border-sage-500 shadow-sm')
                        .addClass('bg-card text-secondary border-theme');
                    $(this).removeClass('bg-card text-secondary border-theme').addClass(
                        'bg-sage-600 text-white border-sage-600 dark:bg-sage-500 dark:border-sage-500 shadow-sm');
                    $('.role-panel').addClass('hidden');
                    $(`[data-role-panel="${roleId}"]`).removeClass('hidden');
                });

                // Delete role confirmation handler
                @foreach ($roles as $role)
                    @if (!in_array($role->name, ['admin', 'manager', 'cashier', 'stock_clerk']))
                        (function() {
                            const input = document.getElementById('confirm-delete-role-{{ $role->id }}');
                            const confirmBtn = document.getElementById('delete-role-confirm-{{ $role->id }}');
                            const form = document.getElementById('delete-role-form-{{ $role->id }}');

                            if (input && confirmBtn) {
                                input.addEventListener('input', function() {
                                    const typed = this.value.trim().toUpperCase();
                                    if (typed === 'DELETE') {
                                        confirmBtn.disabled = false;
                                        confirmBtn.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
                                    } else {
                                        confirmBtn.disabled = true;
                                        confirmBtn.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
                                    }
                                });

                                // Reset on modal close
                                const modal = document.getElementById('delete-role-{{ $role->id }}');
                                if (modal) {
                                    const observer = new MutationObserver(function() {
                                        if (modal.classList.contains('hidden')) {
                                            if (input) {
                                                input.value = '';
                                                confirmBtn.disabled = true;
                                                confirmBtn.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
                                            }
                                        }
                                    });
                                    observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
                                }
                            }
                        })();
                    @endif
                @endforeach
            });
        </script>
    @endpush
@endsection
