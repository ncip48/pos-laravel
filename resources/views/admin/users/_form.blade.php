@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-secondary mb-1.5">
            Name <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                <x-icon name="user" class="w-4 h-4" />
            </div>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                placeholder="Enter full name"
                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition @error('name') border-red-500 ring-2 ring-red-500 @enderror">
        </div>
        @error('name')
            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1.5">
                Email <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                    <x-icon name="mail" class="w-4 h-4" />
                </div>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                    placeholder="user@example.com"
                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition @error('email') border-red-500 ring-2 ring-red-500 @enderror">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                    {{ $message }}
                </p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1.5">Phone</label>
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                    <x-icon name="phone" class="w-4 h-4" />
                </div>
                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                    placeholder="e.g. 08123456789"
                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition @error('phone') border-red-500 ring-2 ring-red-500 @enderror">
            </div>
            @error('phone')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-secondary mb-1.5">
            Password @if (isset($user))
                <span class="text-secondary opacity-60 font-normal text-xs">(leave blank to keep current password)</span>
            @else
                <span class="text-red-500">*</span>
            @endif
        </label>
        <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                <x-icon name="lock" class="w-4 h-4" />
            </div>
            <input type="password" name="password" placeholder="{{ isset($user) ? 'Leave blank to keep current' : 'Minimum 8 characters' }}"
                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition @error('password') border-red-500 ring-2 ring-red-500 @enderror"
                {{ isset($user) ? '' : 'required' }}>
        </div>
        @error('password')
            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-secondary mb-1.5">
            Role <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                <x-icon name="shield-check" class="w-4 h-4" />
            </div>
            <select name="role" required
                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer @error('role') border-red-500 ring-2 ring-red-500 @enderror">
                <option value="">Select role...</option>
                @php
                    $selectedRole = old('role', isset($user) ? $user->roles->first()?->name : '');
                @endphp

                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>
                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    </option>
                @endforeach
            </select>
            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                <x-icon name="chevron-down" class="w-4 h-4" />
            </div>
        </div>
        @error('role')
            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                {{ $message }}
            </p>
        @enderror
    </div>

    <label class="flex items-center gap-3 text-sm text-secondary cursor-pointer group">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))
            class="w-4 h-4 rounded border-theme text-sage-600 dark:text-sage-400 focus:ring-sage-400 dark:focus:ring-sage-500 focus:ring-2 transition">
        <span class="group-hover:text-primary transition">Active <span class="text-xs text-secondary opacity-60 font-normal">(uncheck to immediately log this user out and block sign-in)</span></span>
    </label>
</div>
