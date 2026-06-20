<?php

use App\Http\Middleware\CheckRegisterSession;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\LogActivityMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Module route files are required explicitly (rather than
            // globbed) so load order is predictable and each file's
            // responsibility is visible from this one place.
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/pos.php'));
            Route::prefix('api')->middleware('api')->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'log.activity' => LogActivityMiddleware::class,
            'register.session' => CheckRegisterSession::class,

            // Spatie Permission
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Applied to every authenticated web request -- a deactivated
        // account is logged out immediately rather than waiting for
        // session expiry, and every request carries IP/UA into the
        // activity log context.
        $middleware->web(append: [
            EnsureUserIsActive::class,
            LogActivityMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
