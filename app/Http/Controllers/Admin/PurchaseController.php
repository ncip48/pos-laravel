<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\ReceivePurchaseRequest;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\Middleware;

class PurchaseController extends Controller  implements HasMiddleware
{
    public function __construct(
        private readonly PurchaseService $purchaseService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchases.view', only: ['index', 'show']),
            new Middleware('permission:purchases.create', only: ['create', 'store']),
            new Middleware('permission:purchases.update', only: ['edit', 'update']),
            new Middleware('permission:purchases.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $filters = request()->only(['search', 'status', 'supplier_id', 'warehouse_id']);
        $purchases = $this->purchaseService->paginate($filters, 15);
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers', 'warehouses', 'filters'));
    }

    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.purchases.create', compact('suppliers', 'warehouses'));
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $items = array_map(fn(array $item) => [
            'product_id' => $item['product_id'],
            'quantity_ordered' => $item['quantity_ordered'],
            'unit_cost_cents' => Money::fromUnits($item['unit_cost'])->amount(),
        ], $validated['items']);

        $purchase = $this->purchaseService->create([
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'order_date' => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'discount_cents' => Money::fromUnits($validated['discount'] ?? 0)->amount(),
            'tax_cents' => Money::fromUnits($validated['tax'] ?? 0)->amount(),
        ], $items, $request->user());

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->purchase_number} created as draft.");
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'warehouse', 'user', 'items.product.unit', 'stockMovements']);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function markOrdered(Purchase $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        $this->purchaseService->markOrdered($purchase);

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('success', 'Purchase marked as ordered.');
    }

    public function receive(ReceivePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('receive', $purchase);

        // Filter out zero/empty entries so receiveItems() only processes
        // lines the user actually entered a quantity for.
        $receivedQuantities = array_filter(
            array_map('intval', $request->validated('received')),
            fn(int $qty) => $qty > 0
        );

        if (empty($receivedQuantities)) {
            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with('error', 'Enter at least one quantity greater than zero to receive.');
        }

        $this->purchaseService->receiveItems($purchase, $receivedQuantities, $request->user());

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('success', 'Stock received and inventory updated.');
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        $this->authorize('cancel', $purchase);

        $this->purchaseService->cancel($purchase);

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('success', 'Purchase order cancelled.');
    }
}
