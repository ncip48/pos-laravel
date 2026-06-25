@extends('layouts.admin')

@section('page-title', 'Users')
@section('breadcrumb', 'User Management')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="users" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Users</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $users->total() }} total users</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ $users->where('is_active', true)->count() }} active
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('roles.manage')
                    <a href="{{ route('admin.roles.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                        <x-icon name="shield-check" class="w-4 h-4" />
                        Manage Roles
                    </a>
                @endcan
                @can('create', \App\Models\User::class)
                    <a href="{{ route('admin.users.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200 group">
                        <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                        Add User
                    </a>
                @endcan
            </div>
        </div>

        {{-- Users Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-sage-50 dark:bg-sage-900/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="user" class="w-3.5 h-3.5" />
                                    User
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="shield-check" class="w-3.5 h-3.5" />
                                    Role
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="phone" class="w-3.5 h-3.5" />
                                    Phone
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
                        @forelse ($users as $user)
                            <tr class="hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->avatarUrl() }}" class="w-9 h-9 rounded-xl object-cover border border-theme">
                                        <div>
                                            <p class="font-medium text-primary">{{ $user->name }}</p>
                                            <p class="text-xs text-secondary">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge color="gray">
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="shield-check" class="w-3 h-3" />
                                            {{ ucwords(str_replace('_', ' ', $user->roles->first()?->name ?? 'No role')) }}
                                        </span>
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-secondary">
                                    {{ $user->phone ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$user->is_active ? 'success' : 'gray'">
                                        <span class="flex items-center gap-1.5">
                                            @if ($user->is_active)
                                                <span class="w-1.5 h-1.5 rounded-full bg-sage-500 dark:bg-sage-400 animate-pulse"></span>
                                            @endif
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        @can('update', $user)
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                                class="p-1.5 rounded-lg text-secondary hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 transition"
                                                title="Edit User">
                                                <x-icon name="pencil" class="w-4 h-4" />
                                            </a>
                                        @endcan
                                        @can('delete', $user)
                                            <button type="button" data-modal-target="delete-user-{{ $user->id }}"
                                                class="p-1.5 rounded-lg text-secondary hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition"
                                                title="Delete User">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            {{-- Delete Modal --}}
                            @can('delete', $user)
                                <x-modal id="delete-user-{{ $user->id }}" title="Delete User" icon="danger">
                                    <div class="space-y-4">
                                        <div class="flex items-start gap-4 p-4 bg-red-50/50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800/50">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                                                <x-icon name="alert-triangle" class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                                    Delete <strong class="text-red-900 dark:text-red-100">{{ $user->name }}</strong>?
                                                </p>
                                                <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                                                    This will permanently delete the user account and remove all associated data.
                                                    This action cannot be undone.
                                                </p>
                                                @if ($user->id === auth()->id())
                                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 flex items-center gap-1.5">
                                                        <x-icon name="exclamation" class="w-4 h-4" />
                                                        You cannot delete your own account.
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($user->id !== auth()->id())
                                            <div class="space-y-2">
                                                <label class="text-xs font-medium text-secondary">
                                                    Type <span class="font-bold text-primary">DELETE</span> to confirm
                                                </label>
                                                <input type="text" id="confirm-delete-{{ $user->id }}"
                                                    class="w-full rounded-xl border-theme px-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                                                    placeholder="Type DELETE to confirm..." autocomplete="off">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" data-modal-close="delete-user-{{ $user->id }}"
                                            class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                                            Cancel
                                        </button>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" id="delete-form-{{ $user->id }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" id="delete-confirm-{{ $user->id }}" disabled
                                                    class="rounded-xl bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                                    <x-icon name="trash" class="w-4 h-4" />
                                                    Delete User
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </x-modal>
                            @endcan
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-2xl bg-sage-100/30 dark:bg-sage-800/20 flex items-center justify-center mb-4">
                                            <x-icon name="users" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No users found</p>
                                        <p class="text-sm text-secondary mt-1">Start by adding your first user</p>
                                        @can('create', \App\Models\User::class)
                                            <a href="{{ route('admin.users.create') }}"
                                                class="inline-flex items-center gap-2 mt-4 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200">
                                                <x-icon name="plus" class="w-4 h-4" />
                                                Add User
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ $users->firstItem() ?? 0 }}</span>
                        to <span class="font-medium text-primary">{{ $users->lastItem() ?? 0 }}</span>
                        of <span class="font-medium text-primary">{{ $users->total() }}</span> users
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Delete confirmation handler
                @foreach ($users as $user)
                    @can('delete', $user)
                        @if ($user->id !== auth()->id())
                            (function() {
                                const input = document.getElementById('confirm-delete-{{ $user->id }}');
                                const confirmBtn = document.getElementById('delete-confirm-{{ $user->id }}');
                                const form = document.getElementById('delete-form-{{ $user->id }}');

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
                                    const modal = document.getElementById('delete-user-{{ $user->id }}');
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
                    @endcan
                @endforeach
            });
        </script>
    @endpush
@endsection
