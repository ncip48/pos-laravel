@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main fields --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Information --}}
        <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-5">
                <div
                    class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="cube" class="w-4 h-4" />
                </div>
                <h3 class="font-semibold text-primary">Basic Information</h3>
                <span class="ml-auto text-xs text-secondary bg-sage-100 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">Required
                    fields marked *</span>
            </div>

            <div class="space-y-4">
                {{-- Product Name --}}
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="tag" class="w-4 h-4" />
                        </div>
                        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                            placeholder="Enter product name..."
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition @error('name') border-red-500 ring-2 ring-red-500 @enderror">
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- SKU & Barcode --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">
                            SKU
                            @if (!isset($product))
                                <span class="text-xs text-secondary opacity-60 font-normal">(auto-generated if
                                    blank)</span>
                            @endif
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="barcode" class="w-4 h-4" />
                            </div>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                                placeholder="e.g. PRD-001"
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition @error('sku') border-red-500 ring-2 ring-red-500 @enderror">
                        </div>
                        @error('sku')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">Barcode</label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="qrcode" class="w-4 h-4" />
                            </div>
                            <input type="text" name="barcode" id="barcode-input"
                                value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="e.g. 1234567890"
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition @error('barcode') border-red-500 ring-2 ring-red-500 @enderror">
                            <button type="button" id="generate-barcode"
                                class="absolute right-2 top-1/2 -translate-y-1/2 px-2.5 py-1 text-xs font-medium text-sage-600 dark:text-sage-400 hover:text-sage-800 dark:hover:text-sage-300 transition">
                                Generate
                            </button>
                        </div>
                        @error('barcode')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Category & Unit --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">Category</label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="folder" class="w-4 h-4" />
                            </div>
                            <select name="category_id"
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition appearance-none cursor-pointer">
                                <option value="">Select category...</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? null) == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                                <x-icon name="chevron-down" class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">
                            Unit <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="scale" class="w-4 h-4" />
                            </div>
                            <select name="unit_id" required
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition appearance-none cursor-pointer">
                                <option value="">Select unit...</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id ?? null) == $unit->id)>
                                        {{ $unit->name }} ({{ $unit->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                                <x-icon name="chevron-down" class="w-4 h-4" />
                            </div>
                        </div>
                        @error('unit_id')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">Description</label>
                    <div class="relative">
                        <textarea name="description" rows="3" placeholder="Enter product description..."
                            class="w-full rounded-xl border-theme px-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition resize-y min-h-[80px]">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-5">
                <div
                    class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="cash" class="w-4 h-4" />
                </div>
                <h3 class="font-semibold text-primary">Pricing</h3>
                <span class="ml-auto text-xs text-secondary bg-sage-100 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">Cost
                    &amp; Selling</span>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">
                            Cost Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary font-medium text-sm">{{ $currencySymbol ?? 'Rp' }}</span>
                            <input type="number" step="0.01" min="0" name="cost_price" id="cost_price"
                                value="{{ old('cost_price', isset($product) ? $product->costPrice()->units() : '') }}"
                                required placeholder="0"
                                class="w-full rounded-xl border-theme pl-8 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition @error('cost_price') border-red-500 ring-2 ring-red-500 @enderror">
                        </div>
                        @error('cost_price')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">
                            Selling Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary font-medium text-sm">{{ $currencySymbol ?? 'Rp' }}</span>
                            <input type="number" step="0.01" min="0" name="selling_price"
                                id="selling_price"
                                value="{{ old('selling_price', isset($product) ? $product->sellingPrice()->units() : '') }}"
                                required placeholder="0"
                                class="w-full rounded-xl border-theme pl-8 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition @error('selling_price') border-red-500 ring-2 ring-red-500 @enderror">
                        </div>
                        @error('selling_price')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Margin Indicator --}}
                <div id="margin-indicator"
                    class="hidden rounded-xl px-4 py-3 bg-sage-50 dark:bg-sage-900/20 border border-sage-200 dark:border-sage-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-secondary">Gross Margin</span>
                        <span id="margin-percentage" class="text-sm font-bold text-primary font-mono-num">0%</span>
                    </div>
                    <div class="mt-1.5 w-full bg-sage-200 dark:bg-sage-700 rounded-full h-1.5">
                        <div id="margin-bar" class="bg-sage-600 dark:bg-sage-400 h-1.5 rounded-full transition-all duration-300"
                            style="width: 0%"></div>
                    </div>
                </div>

                {{-- Below Cost Warning --}}
                <div id="below-cost-warning"
                    class="hidden rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3">
                    <label class="flex items-start gap-3 text-sm text-amber-800 dark:text-amber-200 cursor-pointer">
                        <input type="checkbox" name="confirm_below_cost" value="1"
                            class="mt-0.5 rounded border-amber-300 dark:border-amber-600 text-amber-600 dark:text-amber-500 focus:ring-amber-400 dark:focus:ring-amber-500 focus:ring-2 transition">
                        <span>Selling price is below cost price. I confirm this is intentional <span
                                class="text-xs opacity-70">(e.g. clearance sale)</span>.</span>
                    </label>
                </div>

                {{-- Tax Rate --}}
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">
                        Tax Rate (%)
                        <span class="text-xs text-secondary opacity-60 font-normal">— leave blank to use store
                            default</span>
                    </label>
                    <div class="relative max-w-[200px]">
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate_percent"
                            value="{{ old('tax_rate_percent', $product->tax_rate_percent ?? '') }}"
                            placeholder="e.g. 10"
                            class="w-full rounded-xl border-theme px-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition pr-10">
                        <span
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary text-sm font-medium">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Product Image --}}
        <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="photo" class="w-4 h-4" />
                </div>
                <h3 class="font-semibold text-primary">Product Image</h3>
            </div>

            <div id="image-preview-wrap"
                class="aspect-square rounded-xl border-2 border-dashed border-theme flex items-center justify-center overflow-hidden bg-sage-50/30 dark:bg-sage-900/20 hover:border-sage-400 dark:hover:border-sage-500 transition cursor-pointer relative group">
                @if (isset($product) && $product->image_path)
                    <img id="image-preview" src="{{ asset('storage/' . $product->image_path) }}"
                        class="w-full h-full object-cover">
                    <div
                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <span class="text-white text-sm font-medium">Change Image</span>
                    </div>
                @else
                    <div id="image-placeholder" class="text-center text-secondary p-4">
                        <x-icon name="photo" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                        <p class="text-sm font-medium">Drop image here</p>
                        <p class="text-xs opacity-60 mt-1">or click to browse</p>
                        <p class="text-xs opacity-40 mt-2">PNG, JPG, WEBP</p>
                    </div>
                    <img id="image-preview" class="hidden w-full h-full object-cover">
                @endif
            </div>
            <input type="file" name="image" id="image-input" accept="image/png,image/jpeg,image/webp"
                class="hidden">
            <button type="button" id="remove-image"
                class="mt-3 w-full rounded-xl border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium px-4 py-2 transition {{ isset($product) && $product->image_path ? '' : 'hidden' }}">
                Remove Image
            </button>
            @error('image')
                <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                    <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Opening Stock (for new products) --}}
        @if (!isset($product))
            <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <x-icon name="package" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Opening Stock</h3>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">Warehouse</label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="warehouse" class="w-4 h-4" />
                            </div>
                            <select name="warehouse_id"
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition appearance-none cursor-pointer">
                                <option value="">Skip for now</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected($warehouse->is_default)>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                                <x-icon name="chevron-down" class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">Initial Quantity</label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="inbox" class="w-4 h-4" />
                            </div>
                            <input type="number" min="0" name="initial_stock"
                                value="{{ old('initial_stock', 0) }}" placeholder="0"
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Inventory & Status --}}
        <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="settings" class="w-4 h-4" />
                </div>
                <h3 class="font-semibold text-primary">Inventory &amp; Status</h3>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">Minimum Stock Level</label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="alert-triangle" class="w-4 h-4" />
                        </div>
                        <input type="number" min="0" name="min_stock_level"
                            value="{{ old('min_stock_level', $product->min_stock_level ?? 5) }}" placeholder="5"
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition">
                    </div>
                    <p class="mt-1.5 text-xs text-secondary opacity-60 flex items-center gap-1">
                        <x-icon name="info" class="w-3 h-3" />
                        Alerts trigger when stock falls to or below this number
                    </p>
                </div>

                <label class="flex items-center gap-3 text-sm text-secondary cursor-pointer group">
                    <input type="checkbox" name="track_stock" value="1" @checked(old('track_stock', $product->track_stock ?? true))
                        class="w-4 h-4 rounded border-theme text-sage-600 dark:text-sage-400 focus:ring-sage-400 dark:focus:ring-sage-500 focus:ring-2 transition">
                    <span class="group-hover:text-primary transition">Track stock for this product</span>
                </label>

                <div>
                    <label class="block text-sm font-medium text-secondary mb-1.5">Status</label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                            <x-icon name="shield-check" class="w-4 h-4" />
                        </div>
                        <select name="status"
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 focus:border-sage-400 transition appearance-none cursor-pointer">
                            <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $product->status ?? '') === 'inactive')>Inactive</option>
                            <option value="discontinued" @selected(old('status', $product->status ?? '') === 'discontinued')>Discontinued</option>
                        </select>
                        <div
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                            <x-icon name="chevron-down" class="w-4 h-4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
