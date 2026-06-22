@extends('layouts.admin')

@section('page-title', 'New Purchase Order')
@section('breadcrumb', 'Create Purchase Order')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                    <x-icon name="plus" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-primary">New Purchase Order</h2>
                    <div class="flex items-center gap-2 text-sm text-secondary">
                        <a href="{{ route('admin.purchases.index') }}"
                            class="hover:text-sage-600 dark:hover:text-sage-400 transition flex items-center gap-1">
                            <x-icon name="chevron-left" class="w-3 h-3" />
                            Back to Purchases
                        </a>
                        <span class="w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30"></span>
                        <span>Create a new purchase order</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-sage-600 dark:text-sage-400 bg-sage-100/50 dark:bg-sage-800/30 px-3 py-1.5 rounded-full border border-sage-200 dark:border-sage-700">
                <span class="w-1.5 h-1.5 rounded-full bg-sage-500 dark:bg-sage-400 animate-pulse"></span>
                Draft will be saved
            </div>
        </div>

        <form method="POST" action="{{ route('admin.purchases.store') }}" id="purchase-form">
            @csrf

            {{-- Purchase Details --}}
            <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                        <x-icon name="clipboard-list" class="w-4 h-4" />
                    </div>
                    <h3 class="font-semibold text-primary">Purchase Details</h3>
                    <span class="ml-auto text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">Required
                        fields *</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="truck" class="w-4 h-4" />
                            </div>
                            <select name="supplier_id" required
                                class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer @error('supplier_id') border-red-500 ring-2 ring-red-500 @enderror">
                                <option value="">Select supplier...</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                                <x-icon name="chevron-down" class="w-4 h-4" />
                            </div> --}}
                        </div>
                        @error('supplier_id')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">
                            Receiving Warehouse <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="warehouse" class="w-4 h-4" />
                            </div>
                            <select name="warehouse_id" required
                                class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer @error('warehouse_id') border-red-500 ring-2 ring-red-500 @enderror">
                                <option value="">Select warehouse...</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $warehouse->is_default))>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                                <x-icon name="chevron-down" class="w-4 h-4" />
                            </div> --}}
                        </div>
                        @error('warehouse_id')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">
                            Order Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="calendar" class="w-4 h-4" />
                            </div>
                            <input type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}"
                                required
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1.5">Expected Date</label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                                <x-icon name="clock" class="w-4 h-4" />
                            </div>
                            <input type="date" name="expected_date" value="{{ old('expected_date') }}"
                                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-secondary mb-1.5">Notes</label>
                    <div class="relative">
                        <div class="absolute left-3 top-3 text-secondary opacity-40">
                            <x-icon name="info" class="w-4 h-4" />
                        </div>
                        <textarea name="notes" rows="2" placeholder="Add any additional notes about this purchase order..."
                            class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition resize-none">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Items Section --}}
            <div class="bg-card rounded-2xl border border-theme p-6 shadow-sm hover:shadow-md transition-shadow mt-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-sage-100 dark:bg-sage-800/30 text-sage-600 dark:text-sage-400 flex items-center justify-center">
                            <x-icon name="cube" class="w-4 h-4" />
                        </div>
                        <h3 class="font-semibold text-primary">Order Items</h3>
                        <span class="text-xs text-secondary bg-sage-100/50 dark:bg-sage-800/30 px-2.5 py-1 rounded-full border border-sage-200 dark:border-sage-700">
                            <span id="item-count">0</span> items
                        </span>
                    </div>
                    <button type="button" id="add-item-btn"
                        class="inline-flex items-center gap-2 rounded-xl border border-theme hover:bg-sage-100 dark:hover:bg-sage-800/30 hover:text-sage-700 dark:hover:text-sage-300 px-4 py-2 text-sm font-medium text-secondary transition group">
                        <x-icon name="plus" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                        Add Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm" id="items-table">
                        <thead>
                            <tr
                                class="text-left text-xs font-medium text-secondary uppercase tracking-wider border-b border-theme">
                                <th class="pb-3 pr-2">
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="tag" class="w-3.5 h-3.5" />
                                        Product
                                    </span>
                                </th>
                                <th class="pb-3 px-2 w-28">
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="inbox" class="w-3.5 h-3.5" />
                                        Quantity
                                    </span>
                                </th>
                                <th class="pb-3 px-2 w-32">
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="cash" class="w-3.5 h-3.5" />
                                        Unit Cost
                                    </span>
                                </th>
                                <th class="pb-3 px-2 w-32 text-right">
                                    <span class="flex items-center justify-end gap-1.5">
                                        <x-icon name="receipt" class="w-3.5 h-3.5" />
                                        Subtotal
                                    </span>
                                </th>
                                <th class="pb-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody" class="divide-y divide-theme"></tbody>
                    </table>
                </div>
                @error('items')
                    <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                        <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                        {{ $message }}
                    </p>
                @enderror

                {{-- Totals --}}
                <div class="mt-4 pt-4 border-t border-theme flex flex-col sm:flex-row sm:items-start sm:justify-end">
                    <div class="w-full sm:w-72 space-y-2 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-secondary font-medium">Subtotal</span>
                            <span id="subtotal-display" class="font-mono-num font-semibold text-primary">0.00</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-t border-theme/50">
                            <label class="text-secondary font-medium">Discount</label>
                            <div class="relative w-32">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-secondary text-xs">Rp</span>
                                <input type="number" step="0.01" min="0" name="discount"
                                    value="{{ old('discount', 0) }}"
                                    class="w-full rounded-xl border-theme pl-5 pr-3 py-1.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm text-right font-mono-num focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-1 border-t border-theme/50">
                            <label class="text-secondary font-medium">Tax</label>
                            <div class="relative w-32">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-secondary text-xs">Rp</span>
                                <input type="number" step="0.01" min="0" name="tax"
                                    value="{{ old('tax', 0) }}"
                                    class="w-full rounded-xl border-theme pl-5 pr-3 py-1.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm text-right font-mono-num focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-2 border-t-2 border-theme">
                            <span class="font-semibold text-primary text-base">Total</span>
                            <span id="total-display" class="font-mono-num font-bold text-primary text-lg">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div
                class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 bg-card rounded-2xl border border-theme p-4 shadow-sm">
                <div class="text-sm text-secondary">
                    <span class="font-medium text-primary">*</span> Required fields
                    <span class="inline-block w-1 h-1 rounded-full bg-sage-300 dark:bg-sage-600 opacity-30 mx-2"></span>
                    Items can be added after saving
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin.purchases.index') }}"
                        class="flex-1 sm:flex-none rounded-xl border border-theme text-sm font-medium px-6 py-2.5 text-secondary hover:bg-sage-50 dark:hover:bg-sage-900/20 hover:text-primary transition text-center">
                        Cancel
                    </a>
                    <button type="submit"
                        class="flex-1 sm:flex-none rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-sm font-medium px-6 py-2.5 text-white shadow-sm hover:shadow-md transition flex items-center justify-center gap-2 group">
                        <x-icon name="save" class="w-4 h-4 group-hover:scale-110 transition-transform duration-300" />
                        Save as Draft
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Row template --}}
    <template id="item-row-template">
        <tr class="item-row">
            <td class="py-2 pr-2">
                <div class="relative">
                    <select name="items[__INDEX__][product_id]"
                        class="product-select w-full rounded-xl border-theme pl-3 pr-8 py-2 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer"
                        required>
                        <option value="">Select product...</option>
                    </select>
                    {{-- <div class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </div> --}}
                </div>
            </td>
            <td class="py-2 px-2">
                <input type="number" name="items[__INDEX__][quantity_ordered]" min="1" value="1"
                    class="qty-input w-full rounded-xl border-theme px-3 py-2 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num text-right focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition"
                    required>
            </td>
            <td class="py-2 px-2">
                <div class="relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-secondary text-xs">Rp</span>
                    <input type="number" step="0.01" min="0" name="items[__INDEX__][unit_cost]"
                        value="0"
                        class="cost-input w-full rounded-xl border-theme pl-5 pr-3 py-2 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm font-mono-num text-right focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition"
                        required>
                </div>
            </td>
            <td class="py-2 px-2 text-right font-mono-num font-medium text-primary line-subtotal">0.00</td>
            <td class="py-2 text-right">
                <button type="button"
                    class="remove-item-btn p-1.5 rounded-lg text-secondary hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        const PRODUCTS_URL = '{{ route('admin.products.index') }}';
        let itemIndex = 0;

        $(function() {
            function addItemRow() {
                const template = document.getElementById('item-row-template').innerHTML.replaceAll('__INDEX__',
                    itemIndex);
                const $row = $(template);
                $row.find('.product-select').append($('#product-options-source').html());
                $('#items-tbody').append($row);
                itemIndex++;
                updateItemCount();
                recalcTotals();

                // Auto-focus the product select
                $row.find('.product-select').focus();
            }

            function updateItemCount() {
                const count = $('.item-row').length;
                $('#item-count').text(count);
            }

            function recalcTotals() {
                let subtotal = 0;
                $('.item-row').each(function() {
                    const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                    const cost = parseFloat($(this).find('.cost-input').val()) || 0;
                    const lineTotal = qty * cost;
                    $(this).find('.line-subtotal').text(lineTotal.toFixed(2));
                    subtotal += lineTotal;
                });

                const discount = parseFloat($('input[name=discount]').val()) || 0;
                const tax = parseFloat($('input[name=tax]').val()) || 0;
                const total = Math.max(0, subtotal - discount + tax);

                $('#subtotal-display').text(subtotal.toFixed(2));
                $('#total-display').text(total.toFixed(2));
            }

            // Add item button
            $('#add-item-btn').on('click', addItemRow);

            // Recalculate on input changes
            $(document).on('input', '.qty-input, .cost-input, input[name=discount], input[name=tax]', recalcTotals);

            // Remove item
            $(document).on('click', '.remove-item-btn', function() {
                const $row = $(this).closest('tr');
                if ($('.item-row').length <= 1) {
                    // Clear the row instead of removing
                    $row.find('.product-select').val('');
                    $row.find('.qty-input').val(1);
                    $row.find('.cost-input').val(0);
                    recalcTotals();
                } else {
                    $row.remove();
                    updateItemCount();
                    recalcTotals();
                }
            });

            // Keyboard shortcut: Ctrl+Enter to add item
            $(document).on('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    addItemRow();
                }
            });

            // Auto-add first row
            addItemRow();

            // Validate at least one item has a product selected
            $('#purchase-form').on('submit', function(e) {
                let hasProduct = false;
                $('.product-select').each(function() {
                    if ($(this).val()) {
                        hasProduct = true;
                        return false;
                    }
                });
                if (!hasProduct) {
                    e.preventDefault();
                    alert('Please add at least one item to the purchase order.');
                }
            });
        });
    </script>

    {{-- Product options source --}}
    <div id="product-options-source" class="hidden">
        @foreach (\App\Models\Product::active()->orderBy('name')->get(['id', 'name', 'sku']) as $product)
            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
        @endforeach
    </div>
@endpush
