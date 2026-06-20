<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\ProductService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Thin by design: every method validates (via Form Request), calls exactly
 * one ProductService method, and returns a response. All business logic
 * (slug generation, SKU generation, image processing, opening-stock seeding)
 * lives in ProductService -- this controller has zero direct DB access.
 */
class ProductController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index', 'show']),
            new Middleware('permission:products.create', only: ['create', 'store']),
            new Middleware('permission:products.update', only: ['edit', 'update']),
            new Middleware('permission:products.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $filters = request()->only(['search', 'category_id', 'status', 'low_stock_only', 'sort_by', 'sort_dir']);

        $products = $this->productService->paginate($filters, 15);
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'filters'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'units', 'warehouses'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data = $this->normalizeMoneyFields($data);

        $this->productService->create($data, $request->file('image'), $request->user());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data = $this->normalizeMoneyFields($data);

        $this->productService->update($product, $data, $request->file('image'));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Converts the form's human-entered decimal prices ("19.99") into
     * integer cents before they reach the Service/Repository layer -- the
     * boundary between "what a human types" and "what gets stored" is kept
     * here, at the very edge of the request lifecycle, so cents never leak
     * back out into a float anywhere downstream.
     */
    private function normalizeMoneyFields(array $data): array
    {
        $data['cost_price_cents'] = Money::fromUnits($data['cost_price'])->cents();
        $data['selling_price_cents'] = Money::fromUnits($data['selling_price'])->cents();
        unset($data['cost_price'], $data['selling_price'], $data['confirm_below_cost']);

        return $data;
    }
}
