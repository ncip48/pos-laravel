@extends('layouts.admin')

@section('page-title', 'New Purchase Order')

@section('content')
    <div class="">
        <div class="mb-5">
            <a href="{{ route('admin.purchases.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back to
                Purchases</a>
        </div>

        <form method="POST" action="{{ route('admin.purchases.store') }}" id="purchase-form">
            @csrf

            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
                <h3 class="font-semibold text-slate-900">Purchase Details</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Supplier <span
                                class="text-red-500">*</span></label>
                        <select name="supplier_id" required
                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select supplier...</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Receiving Warehouse <span
                                class="text-red-500">*</span></label>
                        <select name="warehouse_id" required
                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select warehouse...</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected($warehouse->is_default)>{{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Order Date <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}"
                            required
                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Expected Date</label>
                        <input type="date" name="expected_date" value="{{ old('expected_date') }}"
                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5 mt-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Items</h3>
                    <button type="button" id="add-item-btn"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-700">
                        <x-icon name="plus" class="w-4 h-4" /> Add Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm" id="items-table">
                        <thead>
                            <tr class="text-left text-xs font-medium text-slate-500 border-b border-slate-200">
                                <th class="pb-2 pr-2">Product</th>
                                <th class="pb-2 px-2 w-28">Quantity</th>
                                <th class="pb-2 px-2 w-32">Unit Cost</th>
                                <th class="pb-2 px-2 w-32 text-right">Subtotal</th>
                                <th class="pb-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                @error('items')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-4 flex justify-end">
                    <div class="w-64 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span id="subtotal-display" class="font-mono-num font-medium">0.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <label class="text-slate-500">Discount</label>
                            <input type="number" step="0.01" min="0" name="discount"
                                value="{{ old('discount', 0) }}"
                                class="w-24 rounded-lg border-slate-300 text-sm text-right font-mono-num">
                        </div>
                        <div class="flex justify-between items-center">
                            <label class="text-slate-500">Tax</label>
                            <input type="number" step="0.01" min="0" name="tax" value="{{ old('tax', 0) }}"
                                class="w-24 rounded-lg border-slate-300 text-sm text-right font-mono-num">
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 font-semibold text-base">
                            <span>Total</span>
                            <span id="total-display" class="font-mono-num">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.purchases.index') }}"
                    class="rounded-lg border border-slate-300 text-sm font-medium px-5 py-2.5 text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-5 py-2.5 text-white shadow-sm">Save
                    as Draft</button>
            </div>
        </form>
    </div>

    {{-- Row template, cloned by jQuery --}}
    <template id="item-row-template">
        <tr class="item-row">
            <td class="py-2 pr-2">
                <select name="items[__INDEX__][product_id]"
                    class="product-select w-full rounded-lg border-slate-300 text-sm" required>
                    <option value="">Select product...</option>
                </select>
            </td>
            <td class="py-2 px-2">
                <input type="number" name="items[__INDEX__][quantity_ordered]" min="1" value="1"
                    class="qty-input w-full rounded-lg border-slate-300 text-sm font-mono-num" required>
            </td>
            <td class="py-2 px-2">
                <input type="number" step="0.01" min="0" name="items[__INDEX__][unit_cost]" value="0"
                    class="cost-input w-full rounded-lg border-slate-300 text-sm font-mono-num" required>
            </td>
            <td class="py-2 px-2 text-right font-mono-num line-subtotal">0.00</td>
            <td class="py-2 text-right">
                <button type="button" class="remove-item-btn text-slate-400 hover:text-red-600">
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
            // Product options are rendered server-side once into
            // #product-options-source (see bottom of this file) and copied into
            // each cloned row -- no AJAX round-trip needed for an admin-side
            // catalog of this size. If the catalog grows large enough that a
            // preloaded <select> becomes unwieldy, swap this for a live-search
            // endpoint (e.g. reusing ProductSearchController from the POS module)
            // feeding a typeahead instead.

            function addItemRow() {
                const template = document.getElementById('item-row-template').innerHTML.replaceAll('__INDEX__',
                    itemIndex);
                const $row = $(template);
                $row.find('.product-select').append($('#product-options-source').html());
                $('#items-tbody').append($row);
                itemIndex++;
                recalcTotals();
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

            $('#add-item-btn').on('click', addItemRow);
            $(document).on('input', '.qty-input, .cost-input, input[name=discount], input[name=tax]', recalcTotals);
            $(document).on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
                recalcTotals();
                if ($('.item-row').length === 0) addItemRow();
            });

            addItemRow(); // start with one empty row
        });
    </script>

    {{-- Product options source, rendered server-side once and reused for every cloned row --}}
    <div id="product-options-source" class="hidden">
        @foreach (\App\Models\Product::active()->orderBy('name')->get(['id', 'name', 'sku']) as $product)
            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
        @endforeach
    </div>
@endpush
