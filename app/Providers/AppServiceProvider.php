<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->app->bind('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
    }

    public function boot(): void
    {
        // Prevent accidental lazy loading from masking N+1 queries in
        // development -- fails loudly instead of silently issuing extra
        // queries, which matters a lot here given how relationship-heavy
        // reporting and POS catalog queries are.
        \Illuminate\Database\Eloquent\Model::shouldBeStrict(! $this->app->isProduction());
    }
}
