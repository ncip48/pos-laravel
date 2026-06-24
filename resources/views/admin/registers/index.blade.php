@extends('layouts.admin')

@section('page-title', 'POS Registers')
@section('breadcrumb', 'System Configuration')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="shield-check" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">POS Registers</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <span>{{ $registers->count() }} registers configured</span>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-sage-500 dark:bg-sage-400"></span>
                            {{ $registers->where('is_active', true)->count() }} active
                        </span>
                    </div>
                </div>
            </div>
            <button type="button" data-modal-target="create-register"
                class="inline-flex items-center gap-2 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200 group">
                <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                Add Register
            </button>
        </div>

        {{-- New Token Alert --}}
        @if (session('new_register_token'))
            <div class="rounded-2xl bg-sage-50 dark:bg-sage-900/20 border border-sage-200 dark:border-sage-700 px-5 py-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                        <x-icon name="key" class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-sage-800 dark:text-sage-200">Pairing token (shown once — copy it now):</p>
                        <div class="mt-2 flex flex-col sm:flex-row items-start sm:items-center gap-2">
                            <code id="new-token-display"
                                class="flex-1 w-full bg-white dark:bg-sage-900/30 border border-sage-200 dark:border-sage-700 rounded-xl px-4 py-2.5 text-sm font-mono-num text-sage-700 dark:text-sage-300 select-all">{{ session('new_register_token') }}</code>
                            <button type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('new-token-display').textContent)"
                                class="inline-flex items-center gap-2 rounded-xl border border-sage-200 dark:border-sage-700 text-sage-600 dark:text-sage-400 hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 text-sm font-medium px-4 py-2.5 transition">
                                <x-icon name="copy" class="w-4 h-4" />
                                Copy
                            </button>
                        </div>
                        <p class="text-xs text-sage-600 dark:text-sage-400 mt-2">Enter this token in the "Pair This Device" prompt the first time the POS register screen loads on the target device.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Registers Table --}}
        <div class="bg-card rounded-2xl border border-theme overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-theme text-sm">
                    <thead class="bg-sage-50 dark:bg-sage-900/20">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="shield-check" class="w-3.5 h-3.5" />
                                    Register
                                </span>
                            </th>
                            <th class="px-6 py-3.5 text-left font-medium text-xs uppercase tracking-wider text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="barcode" class="w-3.5 h-3.5" />
                                    Code
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
                                    <x-icon name="clock" class="w-3.5 h-3.5" />
                                    Last Seen
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
                        @forelse ($registers as $register)
                            <tr class="hover:bg-sage-50/50 dark:hover:bg-sage-900/20 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-sage-100/50 dark:bg-sage-800/30 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                            <x-icon name="shield-check" class="w-4 h-4 text-sage-600 dark:text-sage-400" />
                                        </div>
                                        <span class="font-medium text-primary">{{ $register->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono-num text-secondary">
                                    {{ $register->code }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sage-100/50 dark:bg-sage-800/30 text-xs font-medium text-sage-700 dark:text-sage-300 border border-sage-200 dark:border-sage-700">
                                        <x-icon name="warehouse" class="w-3 h-3" />
                                        {{ $register->warehouse->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-secondary text-sm">
                                    <div class="flex items-center gap-1.5">
                                        <x-icon name="clock" class="w-3.5 h-3.5 text-secondary opacity-40" />
                                        {{ $register->last_seen_at?->diffForHumans() ?? 'Never connected' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :color="$register->is_active ? 'success' : 'gray'">
                                        <span class="flex items-center gap-1.5">
                                            @if ($register->is_active)
                                                <span class="w-1.5 h-1.5 rounded-full bg-sage-500 dark:bg-sage-400 animate-pulse"></span>
                                            @endif
                                            {{ $register->is_active ? 'Active' : 'Deactivated' }}
                                        </span>
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" data-modal-target="regenerate-{{ $register->id }}"
                                            class="text-xs font-medium text-sage-600 dark:text-sage-400 hover:text-sage-800 dark:hover:text-sage-300 transition flex items-center gap-1">
                                            <x-icon name="refresh" class="w-3.5 h-3.5" />
                                            Regenerate Token
                                        </button>
                                        @if ($register->is_active)
                                            <button type="button" data-modal-target="deactivate-{{ $register->id }}"
                                                class="text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition flex items-center gap-1">
                                                <x-icon name="x" class="w-3.5 h-3.5" />
                                                Deactivate
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Regenerate Token Modal --}}
                            <x-modal id="regenerate-{{ $register->id }}" title="Regenerate Token" icon="warning">
                                <div class="space-y-4">
                                    <div class="flex items-start gap-4 p-4 bg-amber-50/50 dark:bg-amber-900/10 rounded-xl border border-amber-200 dark:border-amber-800/50">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                            <x-icon name="alert-triangle" class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                                Regenerate token for <strong class="text-amber-900 dark:text-amber-100">{{ $register->name }}</strong>?
                                            </p>
                                            <p class="text-xs text-amber-600/70 dark:text-amber-300/70 mt-1">
                                                This will invalidate the current pairing token. Any devices currently using this token will need to be re-paired with the new token.
                                            </p>
                                            <div class="mt-2 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                                <x-icon name="exclamation" class="w-4 h-4" />
                                                A new token will be generated and displayed after confirmation.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-secondary">
                                            Type <span class="font-bold text-primary">REGENERATE</span> to confirm
                                        </label>
                                        <input type="text" id="confirm-regenerate-{{ $register->id }}"
                                            class="w-full rounded-xl border-theme px-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                                            placeholder="Type REGENERATE to confirm..." autocomplete="off">
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" data-modal-close="regenerate-{{ $register->id }}"
                                        class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                                        Cancel
                                    </button>
                                    <form method="POST" action="{{ route('admin.registers.regenerate-token', $register) }}" id="regenerate-form-{{ $register->id }}">
                                        @csrf
                                        <button type="submit" id="regenerate-confirm-{{ $register->id }}" disabled
                                            class="rounded-xl bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                            <x-icon name="refresh" class="w-4 h-4" />
                                            Regenerate Token
                                        </button>
                                    </form>
                                </div>
                            </x-modal>

                            {{-- Deactivate Modal --}}
                            @if ($register->is_active)
                                <x-modal id="deactivate-{{ $register->id }}" title="Deactivate Register" icon="danger">
                                    <div class="space-y-4">
                                        <div class="flex items-start gap-4 p-4 bg-red-50/50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800/50">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                                                <x-icon name="alert-triangle" class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                                    Deactivate <strong class="text-red-900 dark:text-red-100">{{ $register->name }}</strong>?
                                                </p>
                                                <p class="text-xs text-red-600/70 dark:text-red-300/70 mt-1">
                                                    This register will no longer be able to pair with devices or process sales.
                                                    Any devices currently paired will lose connection.
                                                </p>
                                                <div class="mt-2 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                                    <x-icon name="exclamation" class="w-4 h-4" />
                                                    This action can be reversed by reactivating the register.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-xs font-medium text-secondary">
                                                Type <span class="font-bold text-primary">DEACTIVATE</span> to confirm
                                            </label>
                                            <input type="text" id="confirm-deactivate-{{ $register->id }}"
                                                class="w-full rounded-xl border-theme px-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                                                placeholder="Type DEACTIVATE to confirm..." autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" data-modal-close="deactivate-{{ $register->id }}"
                                            class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                                            Cancel
                                        </button>
                                        <form method="POST" action="{{ route('admin.registers.deactivate', $register) }}" id="deactivate-form-{{ $register->id }}">
                                            @csrf
                                            <button type="submit" id="deactivate-confirm-{{ $register->id }}" disabled
                                                class="rounded-xl bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                                                <x-icon name="x" class="w-4 h-4" />
                                                Deactivate Register
                                            </button>
                                        </form>
                                    </div>
                                </x-modal>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-2xl bg-sage-100/30 dark:bg-sage-800/20 flex items-center justify-center mb-4">
                                            <x-icon name="shield-check" class="w-10 h-10 text-secondary opacity-30" />
                                        </div>
                                        <p class="text-lg font-medium text-primary">No registers configured yet</p>
                                        <p class="text-sm text-secondary mt-1">Start by adding your first POS register</p>
                                        <button type="button" data-modal-target="create-register"
                                            class="inline-flex items-center gap-2 mt-4 rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md transition-all duration-200">
                                            <x-icon name="plus" class="w-4 h-4" />
                                            Add Register
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
    <x-modal id="create-register" title="Add Register" description="Create a new POS register for offline-capable checkout" icon="plus">
        <form method="POST" action="{{ route('admin.registers.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Register Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="shield-check" class="w-4 h-4" />
                        </div>
                        <input type="text" name="name" placeholder="e.g. Front Counter" required
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Code <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="barcode" class="w-4 h-4" />
                        </div>
                        <input type="text" name="code" placeholder="e.g. WH01-REG01" required
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Warehouse <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="warehouse" class="w-4 h-4" />
                        </div>
                        <select name="warehouse_id" required
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer">
                            @foreach (\App\Models\Warehouse::active()->orderBy('name')->get() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close="create-register"
                    class="rounded-xl border border-theme text-sm font-medium px-5 py-2 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-5 py-2 text-white shadow-sm hover:shadow-md transition flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4" />
                    Create Register
                </button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Regenerate Token confirmation handler
                @foreach ($registers as $register)
                    (function() {
                        const input = document.getElementById('confirm-regenerate-{{ $register->id }}');
                        const confirmBtn = document.getElementById('regenerate-confirm-{{ $register->id }}');
                        const form = document.getElementById('regenerate-form-{{ $register->id }}');

                        if (input && confirmBtn) {
                            input.addEventListener('input', function() {
                                const typed = this.value.trim().toUpperCase();
                                if (typed === 'REGENERATE') {
                                    confirmBtn.disabled = false;
                                    confirmBtn.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
                                } else {
                                    confirmBtn.disabled = true;
                                    confirmBtn.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
                                }
                            });

                            // Reset on modal close
                            const modal = document.getElementById('regenerate-{{ $register->id }}');
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

                    // Deactivate confirmation handler
                    @if ($register->is_active)
                        (function() {
                            const input = document.getElementById('confirm-deactivate-{{ $register->id }}');
                            const confirmBtn = document.getElementById('deactivate-confirm-{{ $register->id }}');
                            const form = document.getElementById('deactivate-form-{{ $register->id }}');

                            if (input && confirmBtn) {
                                input.addEventListener('input', function() {
                                    const typed = this.value.trim().toUpperCase();
                                    if (typed === 'DEACTIVATE') {
                                        confirmBtn.disabled = false;
                                        confirmBtn.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
                                    } else {
                                        confirmBtn.disabled = true;
                                        confirmBtn.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
                                    }
                                });

                                // Reset on modal close
                                const modal = document.getElementById('deactivate-{{ $register->id }}');
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
