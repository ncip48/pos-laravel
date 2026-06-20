<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin (back-office) Routes
|--------------------------------------------------------------------------
| Session-authenticated Blade routes for everything that is NOT the POS
| register screen: dashboard, products, inventory, customers, sales
| history, reports, settings, user/role management, activity logs.
| Full route definitions are added module-by-module in Phase 3+;
| this file is wired into bootstrap/app.php now so the routing
| structure is in place from the start.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Module route groups (Dashboard, Products, Inventory, Customers,
        // Sales, Reports, Settings, Users) are appended here in later phases.
    });
