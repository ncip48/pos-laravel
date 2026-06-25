@extends('layouts.admin')

@section('page-title', 'Edit User')
@section('breadcrumb', 'User Management')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="user-edit" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Edit User</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <a href="{{ route('admin.users.index') }}"
                            class="hover:text-sage-600 dark:hover:text-sage-400 transition flex items-center gap-1">
                            <x-icon name="chevron-left" class="w-3 h-3" />
                            Back to Users
                        </a>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span>Editing: <span class="font-medium text-primary">{{ $user->name }}</span></span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="text-xs text-secondary">{{ $user->email }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <x-badge :color="$user->is_active ? 'success' : 'gray'">
                    <span class="flex items-center gap-1.5">
                        @if ($user->is_active)
                            <span class="w-1.5 h-1.5 rounded-full bg-sage-500 dark:bg-sage-400 animate-pulse"></span>
                        @endif
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </x-badge>
                <span class="text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">
                    <x-icon name="shield-check" class="w-3 h-3 inline mr-1" />
                    {{ ucwords(str_replace('_', ' ', $user->roles->first()?->name ?? 'No role')) }}
                </span>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @method('PUT')
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                        <x-icon name="user" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">User Details</h3>
                    <span class="ml-auto text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">Required fields *</span>
                </div>

                @include('admin.users._form')

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 bg-sage-50/50 dark:bg-sage-900/20 rounded-xl border border-theme p-4">
                    <div class="text-sm text-secondary">
                        <span class="font-medium text-primary">*</span> Required fields
                        <span class="inline-block w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30 mx-2"></span>
                        Leave password blank to keep current password
                    </div>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex-1 sm:flex-none rounded-xl border border-theme text-sm font-medium px-6 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition text-center">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex-1 sm:flex-none rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-6 py-2.5 text-white shadow-sm hover:shadow-md transition flex items-center justify-center gap-2 group">
                            <x-icon name="save" class="w-4 h-4 group-hover:scale-110 transition-transform duration-300" />
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
