<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (consumed by the POS PWA's JavaScript)
|--------------------------------------------------------------------------
| JSON-only endpoints: product search/barcode lookup, catalog snapshot
| (for offline caching), and sale sync (the idempotent offline-sale
| upload endpoint). Authenticated via the normal session (the POS shell
| page is itself session-gated) PLUS the 'register.session' middleware,
| which validates the X-Register-Token header identifying which
| physical/logical register -- and therefore which warehouse -- a
| request belongs to. See app/Http/Middleware/CheckRegisterSession.php.
| Full definitions added in the POS module phase.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'register.session'])
    ->prefix('v1')
    ->group(function () {
        // ProductSearchController, SaleSyncController, CatalogSnapshotController
        // routes appended in the POS module phase.
    });
