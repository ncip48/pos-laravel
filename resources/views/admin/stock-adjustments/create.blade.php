@extends('layouts.admin')

@section('page-title', 'New Stock Adjustment')

@section('content')
    <div class="">
        <div class="mb-5">
            <a href="{{ route('admin.stock-adjustments.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr;
                Back to Adjustments</a>
        </div>

        <form method="POST" action="{{ route('admin.stock-adjustments.store') }}" id="adjustment-form">
            @csrf

            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
                <h3 class="font-semibold text-slate-900">Adjustment Details</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Warehouse <span
                                class="text-red-500">*</span></label>
                        <select name="warehouse_id" id="warehouse-select" required
                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select warehouse...</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reason <span
                                class="text-red-500">*</span></label>
                        <select name="reason" required
                            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="stock_count">Stock Count / Recount</option>
                            <option value="damaged">Damaged Goods</option>
                            <option value="expired">Expired Goods</option>
                            <option value="theft_loss">Theft / Loss</option>
                            <option value="found">Found Stock</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Explain the reason for this adjustment..."></textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5 mt-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Items to Adjust</h3>
                    <button type="button" id="add-item-btn"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-700">
                        <x-icon name="plus" class="w-4 h-4" /> Add Product
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-3">System quantity is fetched automatically once you select a warehouse
                    and product.</p>

                <table class="min-w-full text-sm" id="items-table">
                    <thead>
                        <tr class="text-left text-xs font-medium text-slate-500 border-b border-slate-200">
                            <th class="pb-2 pr-2">Product</th>
                            <th class="pb-2 px-2 w-28 text-right">System Qty</th>
                            <th class="pb-2 px-2 w-28">Counted Qty</th>
                            <th class="pb-2 px-2 w-24 text-right">Difference</th>
                            <th class="pb-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody" class="divide-y divide-slate-100"></tbody>
                </table>
                @error('items')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.stock-adjustments.index') }}"
                    class="rounded-lg border border-slate-300 text-sm font-medium px-5 py-2.5 text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-5 py-2.5 text-white shadow-sm">Save
                    as Draft</button>
            </div>
            <p class="mt-2 text-xs text-slate-500 text-right">Stock levels are only changed after this adjustment is
                approved.</p>
        </form>
    </div>

    <template id="adj-item-row-template">
        <tr class="item-row">
            <td class="py-2 pr-2">
                <select name="items[__INDEX__][product_id]"
                    class="product-select w-full rounded-lg border-slate-300 text-sm" required>
                    <option value="">Select product...</option>
                </select>
            </td>
            <td class="py-2 px-2 text-right font-mono-num system-qty-display">—</td>
            <td class="py-2 px-2">
                <input type="number" name="items[__INDEX__][counted_quantity]" min="0" value="0"
                    class="counted-input w-full rounded-lg border-slate-300 text-sm font-mono-num" required>
                <input type="hidden" name="items[__INDEX__][system_quantity]" class="system-qty-input" value="0">
            </td>
            <td class="py-2 px-2 text-right font-mono-num diff-display">0</td>
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
        let itemIndex = 0;
        // product_id -> { name, sku } for display; stock is fetched per (product, warehouse) via data attributes
        const stockEndpointBase =
            "{{ url('admin/products') }}"; // GET /admin/products/{id}/stock-movements is history, not live qty -- see note below

        $(function() {
            function addItemRow() {
                const template = document.getElementById('adj-item-row-template').innerHTML.replaceAll('__INDEX__',
                    itemIndex);
                const $row = $(template);
                $row.find('.product-select').append($('#product-options-source').html());
                $('#items-tbody').append($row);
                itemIndex++;
            }

            function recalcDiff($row) {
                const system = parseInt($row.find('.system-qty-input').val()) || 0;
                const counted = parseInt($row.find('.counted-input').val()) || 0;
                const diff = counted - system;
                const $diffCell = $row.find('.diff-display');
                $diffCell.text((diff > 0 ? '+' : '') + diff);
                $diffCell.toggleClass('text-emerald-600', diff > 0).toggleClass('text-red-600', diff < 0)
                    .toggleClass('text-slate-500', diff === 0);
            }

            // Fetches current system quantity for the selected product+warehouse
            // combo via the data-stock-{warehouseId} attributes embedded on each
            // <option> (rendered server-side below) -- avoids an extra round-trip
            // per row selection for a typically small admin-side catalog.
            $(document).on('change', '.product-select', function() {
                const $row = $(this).closest('tr');
                const warehouseId = $('#warehouse-select').val();
                const selected = $(this).find('option:selected');
                const stockJson = selected.attr('data-stock') || '{}';
                let stockMap = {};
                try {
                    stockMap = JSON.parse(stockJson);
                } catch (e) {}

                const systemQty = warehouseId && stockMap[warehouseId] !== undefined ? stockMap[
                    warehouseId] : 0;
                $row.find('.system-qty-display').text(systemQty);
                $row.find('.system-qty-input').val(systemQty);
                $row.find('.counted-input').val(systemQty);
                recalcDiff($row);
            });

            $(document).on('input', '.counted-input', function() {
                recalcDiff($(this).closest('tr'));
            });

            $('#warehouse-select').on('change', function() {
                // Re-trigger recompute for all already-selected product rows when
                // warehouse changes, since system quantity is warehouse-specific.
                $('.product-select').each(function() {
                    $(this).trigger('change');
                });
            });

            $('#add-item-btn').on('click', addItemRow);
            $(document).on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
                if ($('.item-row').length === 0) addItemRow();
            });

            addItemRow();
        });
    </script>

    {{-- Product options with per-warehouse stock embedded as a JSON data attribute,
     so the row-level jQuery above can populate "System Qty" without an
     additional AJAX call per selection. --}}
    <div id="product-options-source" class="hidden">
        @foreach (\App\Models\Product::active()->with('stockLevels')->orderBy('name')->get() as $product)
            @php
                $stockByWarehouse = $product->stockLevels->pluck('quantity', 'warehouse_id');
            @endphp
            <option value="{{ $product->id }}" data-stock='{{ $stockByWarehouse->toJson() }}'>{{ $product->name }}
                ({{ $product->sku }})
            </option>
        @endforeach
    </div>
@endpush
