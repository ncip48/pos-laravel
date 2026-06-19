# POS + Inventory System — Architecture

## 1. Stack

| Layer | Choice |
|---|---|
| Backend | Laravel 11.x (PHP 8.2+) |
| Frontend (back-office) | Blade + Tailwind CSS + jQuery |
| Frontend (POS register) | Blade shell + jQuery + Service Worker + IndexedDB (PWA) |
| DB | MySQL 8 |
| Auth | Laravel Breeze (Blade stack), customized |
| Authorization | spatie/laravel-permission (RBAC) |
| PDF | barryvdh/laravel-dompdf (receipts, reports) |
| Excel/CSV | maatwebsite/excel |
| Activity log | spatie/laravel-activitylog |
| Image handling | intervention/image |

## 2. Why Service–Repository, and where the boundaries are

- **Repository**: ONLY persistence concerns — query building, eager loading, pagination. No business rules. One repository per Eloquent aggregate root (not per table — e.g. `Purchase` and `PurchaseItem` share the `PurchaseRepository` because they're always written together transactionally).
- **Service**: business rules, transactions, orchestration across multiple repositories, events. This is where "decrement stock, write a stock movement, then create a sale" lives — not in the controller, not in the model.
- **Controller**: HTTP only — validate via Form Request, call one Service method, return a response. No DB calls, no business logic.
- **Models**: relationships, casts, scopes, accessors. No business logic that spans multiple models.

Contracts (interfaces) for repositories live in `app/Repositories/Contracts`, bound to Eloquent implementations in `RepositoryServiceProvider`. This is the one piece of indirection that pays for itself here: it lets the POS sync engine and a future API swap persistence/test doubles without touching services.

## 3. Folder Structure

```
app/
├── Console/Commands/
│   ├── BackupDatabase.php
│   └── RestoreDatabase.php
├── Enums/
│   ├── PaymentMethod.php
│   ├── PaymentStatus.php
│   ├── SaleStatus.php
│   ├── StockMovementType.php
│   ├── PurchaseStatus.php
│   └── DiscountType.php
├── Events/
│   ├── SaleCompleted.php
│   ├── StockLevelLow.php
│   └── SaleRefunded.php
├── Listeners/
│   ├── DecrementStockOnSale.php
│   ├── LogSaleActivity.php
│   └── NotifyLowStock.php
├── Exceptions/
│   ├── InsufficientStockException.php
│   └── DuplicateSaleException.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                  # back-office, session auth, Blade
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── UnitController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── PurchaseController.php
│   │   │   ├── StockAdjustmentController.php
│   │   │   ├── WarehouseController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── SaleController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SettingController.php
│   │   │   ├── UserController.php
│   │   │   ├── RoleController.php
│   │   │   └── ActivityLogController.php
│   │   ├── Pos/                    # cashier-facing, Blade shell + JSON
│   │   │   ├── RegisterController.php
│   │   │   └── PosController.php
│   │   ├── Api/                    # JSON only — consumed by POS PWA's JS
│   │   │   ├── ProductSearchController.php
│   │   │   ├── SaleSyncController.php
│   │   │   ├── CartPricingController.php
│   │   │   └── CatalogSnapshotController.php
│   │   └── Auth/                   # Breeze-generated, customized
│   ├── Middleware/
│   │   ├── EnsureUserIsActive.php
│   │   ├── LogActivityMiddleware.php
│   │   ├── CheckRegisterSession.php
│   │   └── ForceJsonResponse.php
│   ├── Requests/
│   │   ├── Product/{Store,Update}ProductRequest.php
│   │   ├── Purchase/{Store,Update}PurchaseRequest.php
│   │   ├── Sale/StoreSaleRequest.php
│   │   ├── Sale/SyncOfflineSaleRequest.php
│   │   ├── Customer/{Store,Update}CustomerRequest.php
│   │   └── Settings/UpdateStoreSettingsRequest.php
│   └── Resources/                  # API Resources (JSON shaping for POS PWA)
│       ├── ProductCatalogResource.php
│       └── SaleSyncResultResource.php
├── Models/
│   (see ERD — one model per table, listed in section 4)
├── Policies/
│   ├── ProductPolicy.php
│   ├── PurchasePolicy.php
│   ├── SalePolicy.php
│   ├── UserPolicy.php
│   └── SettingPolicy.php
├── Providers/
│   ├── RepositoryServiceProvider.php
│   └── EventServiceProvider.php
├── Repositories/
│   ├── Contracts/
│   │   ├── ProductRepositoryInterface.php
│   │   ├── StockRepositoryInterface.php
│   │   ├── PurchaseRepositoryInterface.php
│   │   ├── SaleRepositoryInterface.php
│   │   ├── CustomerRepositoryInterface.php
│   │   └── ... (one per aggregate)
│   └── Eloquent/
│       ├── BaseRepository.php       # shared CRUD, implements common contract
│       ├── EloquentProductRepository.php
│       ├── EloquentStockRepository.php
│       ├── EloquentPurchaseRepository.php
│       ├── EloquentSaleRepository.php
│       └── EloquentCustomerRepository.php
├── Services/
│   ├── ProductService.php
│   ├── StockService.php            # the only place stock is mutated
│   ├── PurchaseService.php
│   ├── PosService.php              # cart pricing, checkout orchestration
│   ├── SaleSyncService.php         # offline→online sync, idempotency
│   ├── ReceiptService.php          # PDF/print rendering
│   ├── SaleService.php             # cancel/refund logic
│   ├── ReportService.php
│   ├── DashboardService.php
│   ├── BackupService.php
│   └── ActivityLogService.php
└── Support/
    ├── Money.php                    # integer-cents value object
    └── SkuGenerator.php

database/
├── migrations/  (chronological, see section 5)
├── seeders/
└── factories/

resources/views/
├── layouts/
│   ├── admin.blade.php             # back-office shell (sidebar, topbar)
│   ├── pos.blade.php               # POS shell (PWA manifest, SW registration)
│   └── guest.blade.php             # Breeze auth shell
├── components/                     # Blade components (anonymous + class-based)
│   ├── stat-card.blade.php
│   ├── data-table.blade.php
│   ├── modal.blade.php
│   ├── badge.blade.php
│   └── chart-card.blade.php
├── admin/
│   ├── dashboard/
│   ├── products/
│   ├── categories/
│   ├── suppliers/
│   ├── purchases/
│   ├── stock-adjustments/
│   ├── customers/
│   ├── sales/
│   ├── reports/
│   ├── settings/
│   └── users/
├── pos/
│   ├── register.blade.php          # the actual POS screen (jQuery + IndexedDB)
│   └── receipt-print.blade.php
└── auth/                           # Breeze views, themed

public/
├── sw.js                            # POS service worker
├── manifest.json                    # PWA manifest (POS only)
└── js/
    ├── pos/
    │   ├── db.js                    # IndexedDB wrapper (Dexie)
    │   ├── cart.js
    │   ├── sync-queue.js
    │   └── register.js
    └── admin/
        └── (per-page jQuery)
```

## 4. Core design decisions worth flagging

1. **Stock is never a column on `products`.** It lives in `stock_levels` (product_id, warehouse_id, quantity). Every increment/decrement is also written as an immutable row in `stock_movements` — `stock_levels.quantity` is a derived cache, rebuildable from `stock_movements`. This gives you a real audit trail and makes the "negative stock from offline sync" scenario inspectable instead of mysterious.

2. **Money is stored as integer cents** (`unsignedBigInteger`), never `decimal`/`float`, to avoid floating-point rounding bugs in tax/discount math. A small `Money` value object centralizes formatting.

3. **Sales are append-only.** "Cancel" and "refund" never delete or mutate a `sale`; they create linked `sale_refunds` / set `status` and reverse stock via new `stock_movements`. This matches real accounting practice and is required for the offline-sync trust model anyway.

4. **Offline sale idempotency**: `sales.client_uuid` (generated in the browser at checkout, before any network call) is unique-indexed. The sync endpoint upserts on this key — replaying a sync is always safe.

5. **Price-lock per your requirement**: `sale_items` stores `unit_price` and `unit_cost` as captured at sale time (online or offline), never recomputed from `products` at sync time. A nullable `price_deviation_flagged` boolean + `pos_sync_audits` table records when synced price differs from current catalog price, for back-office review — without blocking the sale.

6. **RBAC via spatie/laravel-permission** rather than a hand-rolled roles table: battle-tested, plays well with Policies, and gives you Blade directives (`@can`) for free in every view.

7. **Warehouses are first-class.** `registers` (physical/logical POS terminals) belong to a `warehouse_id`. Every sale, purchase, and stock movement carries a `warehouse_id`. Reports can roll up or filter by warehouse.

Full ERD and migrations follow in the next files.
