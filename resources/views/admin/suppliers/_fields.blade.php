<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-secondary mb-1.5">
            Supplier Name <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                <x-icon name="truck" class="w-4 h-4" />
            </div>
            <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required
                placeholder="Enter supplier name..."
                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition @error('name') border-red-500 ring-2 ring-red-500 @enderror">
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
            <label class="block text-sm font-medium text-secondary mb-1.5">Contact Person</label>
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                    <x-icon name="user" class="w-4 h-4" />
                </div>
                <input type="text" name="contact_person"
                    value="{{ old('contact_person', $supplier->contact_person ?? '') }}" placeholder="Full name..."
                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1.5">Phone</label>
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                    <x-icon name="phone" class="w-4 h-4" />
                </div>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
                    placeholder="e.g. +62 812 3456 7890"
                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-secondary mb-1.5">Email</label>
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                    <x-icon name="mail" class="w-4 h-4" />
                </div>
                <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
                    placeholder="supplier@example.com"
                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-secondary mb-1.5">Tax ID / NPWP</label>
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                    <x-icon name="barcode" class="w-4 h-4" />
                </div>
                <input type="text" name="tax_id" value="{{ old('tax_id', $supplier->tax_id ?? '') }}"
                    placeholder="e.g. 12.345.678.9-012.345"
                    class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm font-mono-num focus:ring-2 focus:ring-primary-green focus:border-transparent transition">
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-secondary mb-1.5">Address</label>
        <div class="relative">
            <div class="absolute left-3 top-3 text-secondary opacity-40">
                <x-icon name="map-pin" class="w-4 h-4" />
            </div>
            <textarea name="address" rows="2" placeholder="Street address, city, postal code..."
                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-primary-green-light/10 text-sm focus:ring-2 focus:ring-primary-green focus:border-transparent transition resize-none min-h-[80px]">{{ old('address', $supplier->address ?? '') }}</textarea>
        </div>
    </div>

    <label class="flex items-center gap-3 text-sm text-secondary cursor-pointer group">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true))
            class="w-4 h-4 rounded border-theme text-primary-green focus:ring-primary-green focus:ring-2 transition">
        <span class="group-hover:text-primary transition">Active</span>
        <span class="text-xs text-secondary opacity-60">(Inactive suppliers won't appear in purchase orders)</span>
    </label>
</div>
