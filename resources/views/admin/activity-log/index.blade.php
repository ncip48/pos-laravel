@extends('layouts.admin')

@section('page-title', 'Activity Log')
@section('breadcrumb', 'System Audit')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="clock" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">Activity Log</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $activities->total() }} activities recorded</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            System-wide audit trail
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 text-sm bg-card rounded-xl border border-theme px-4 py-2">
                <span class="text-secondary">Latest activity</span>
                @if ($activities->isNotEmpty())
                    <span class="text-primary font-medium">{{ $activities->first()->created_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-card rounded-2xl border border-theme p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search description..."
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="tag" class="w-4 h-4" />
                    </div>
                    <select name="log_name"
                        class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer">
                        <option value="">All categories</option>
                        @foreach ($logNames as $logName)
                            <option value="{{ $logName }}" @selected(($filters['log_name'] ?? '') === $logName)>{{ ucfirst($logName) }}</option>
                        @endforeach
                    </select>
                    {{-- <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </div> --}}
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="user" class="w-4 h-4" />
                    </div>
                    <select name="causer_id"
                        class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(($filters['causer_id'] ?? null) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    {{-- <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </div> --}}
                </div>

                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                        <x-icon name="calendar" class="w-4 h-4" />
                    </div>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                        class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-white text-sm font-medium px-4 py-2.5 transition shadow-sm hover:shadow-md">
                        <x-icon name="filter" class="w-4 h-4" />
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'log_name', 'causer_id', 'from']))
                        <a href="{{ route('admin.activity-log.index') }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-theme text-sm font-medium px-4 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                            <x-icon name="refresh" class="w-4 h-4" />
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            @if ($activities->total() > 0)
                <div class="mt-3 pt-3 border-t border-theme flex items-center justify-between">
                    <span class="text-xs text-secondary">
                        <span class="font-medium text-primary">{{ $activities->total() }}</span> activities found
                    </span>
                    <span class="text-xs text-secondary opacity-60">
                        Latest activities shown first
                    </span>
                </div>
            @endif
        </form>

        {{-- Activities Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-sage-50 dark:bg-sage-900/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="calendar" class="w-3.5 h-3.5" />
                                    Date
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="user" class="w-3.5 h-3.5" />
                                    User
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="tag" class="w-3.5 h-3.5" />
                                    Category
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="file-text" class="w-3.5 h-3.5" />
                                    Description
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="wifi" class="w-3.5 h-3.5" />
                                    IP Address
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme">
                        @forelse ($activities as $activity)
                            <tr class="hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition group">
                                <td class="px-6 py-4 text-secondary text-sm whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <x-icon name="clock" class="w-3.5 h-3.5 text-secondary opacity-40" />
                                        {{ $activity->created_at->format('M d, Y g:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center text-xs font-medium flex-shrink-0">
                                            {{ $activity->causer ? substr($activity->causer->name, 0, 1) : 'S' }}
                                        </div>
                                        <span class="text-secondary">{{ $activity->causer->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge color="gray">
                                        {{ ucfirst($activity->log_name) }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-primary">
                                    {{ $activity->description }}
                                </td>
                                <td class="px-6 py-4 font-mono-num text-secondary">
                                    {{ $activity->ip_address ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-2xl bg-sage-100/30 dark:bg-sage-800/20 flex items-center justify-center mb-4">
                                            <x-icon name="clock" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No activity recorded yet</p>
                                        <p class="text-sm text-secondary mt-1">System activities will appear here as they occur</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activities->hasPages())
                <div class="border-t border-theme px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-secondary">
                        Showing <span class="font-medium text-primary">{{ $activities->firstItem() ?? 0 }}</span>
                        to <span class="font-medium text-primary">{{ $activities->lastItem() ?? 0 }}</span>
                        of <span class="font-medium text-primary">{{ $activities->total() }}</span> activities
                    </div>
                    <div>
                        {{ $activities->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
