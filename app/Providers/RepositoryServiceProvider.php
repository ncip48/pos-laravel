<?php

namespace App\Providers;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentCustomerRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentPurchaseRepository;
use App\Repositories\Eloquent\EloquentSaleRepository;
use App\Repositories\Eloquent\EloquentStockRepository;
use App\Repositories\Eloquent\EloquentSupplierRepository;
use App\Repositories\Eloquent\EloquentUnitRepository;
use Illuminate\Support\ServiceProvider;

/**
 * This is the ONLY place that knows repository contracts are backed by
 * Eloquent. Services and controllers type-hint the *Interface, never the
 * Eloquent* class — that's what lets a repository be swapped (e.g. for a
 * test double, or a different persistence layer for the POS sync queue)
 * without touching a single Service.
 *
 * Registered in bootstrap/providers.php alongside the framework defaults.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(StockRepositoryInterface::class, EloquentStockRepository::class);
        $this->app->bind(PurchaseRepositoryInterface::class, EloquentPurchaseRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, EloquentSaleRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);

        // Concrete-only repositories (no bespoke interface yet — see
        // EloquentUnitRepository docblock). Bound to themselves so they
        // still resolve through the container and remain easy to promote
        // to an interface-bound binding later without touching callers
        // that already type-hint the concrete class... though callers
        // SHOULD still prefer constructor injection of these classes only
        // from within their owning Service, not from Controllers.
        $this->app->bind(EloquentUnitRepository::class, EloquentUnitRepository::class);
        $this->app->bind(EloquentSupplierRepository::class, EloquentSupplierRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
