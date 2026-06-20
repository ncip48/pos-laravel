<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| This file stays minimal: the public landing page and Breeze's
| auth routes (login/register/password reset/profile) live here.
| Everything else is split into routes/admin.php (back-office),
| routes/pos.php (cashier PWA shell), and routes/api.php (JSON,
| consumed by the POS PWA's JS for search/sync/etc) -- see
| bootstrap/app.php for how these are wired in.
*/

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';
