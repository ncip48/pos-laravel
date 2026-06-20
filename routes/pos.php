<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS (cashier) Routes
|--------------------------------------------------------------------------
| Serves the Blade shell that boots the offline-capable POS screen
| (Service Worker registration, IndexedDB init, jQuery cart UI). The
| shell itself is session-authenticated like any other page; once
| loaded, the page's own JS talks to routes/api.php for catalog
| snapshots and sale sync, which can keep working from cached/queued
| data even when this initial page load can't happen again until
| connectivity returns.
| Full definitions added in the POS module phase.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'can:pos.access'])
    ->prefix('pos')
    ->name('pos.')
    ->group(function () {
        // Register screen route(s) appended in the POS module phase.
    });
