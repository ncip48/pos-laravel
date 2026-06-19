<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permission naming convention: "{module}.{action}", e.g. "products.create".
 * This keeps Blade @can() checks and Policy method names predictable and
 * greppable across a large module list, and makes it trivial to grant a
 * custom role a precise subset (e.g. a "stock clerk" who can adjust stock
 * but not see profit reports).
 *
 * Four baseline roles are seeded, matching how a small retail business
 * actually splits responsibilities:
 *  - Admin: full access, including settings/backup and user management.
 *  - Manager: everything except user management and system settings/backup.
 *  - Cashier: POS + customers only -- the role a register login normally uses.
 *  - Stock Clerk: inventory/purchases/stock adjustments, no POS, no reports.
 *
 * Re-running this seeder is safe (firstOrCreate) and intended -- new
 * permissions added in future modules get appended without duplicating
 * existing ones or wiping custom role assignments made via the admin UI.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Cache::forget(config('permission.cache.key') ?? 'spatie.permission.cache');

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Products
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'categories.manage',
            'units.manage',

            // Inventory
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'suppliers.delete',
            'purchases.view',
            'purchases.create',
            'purchases.update',
            'purchases.receive',
            'purchases.cancel',
            'stock-adjustments.view',
            'stock-adjustments.create',
            'stock-adjustments.approve',
            'stock-movements.view',
            'warehouses.manage',

            // POS
            'pos.access',
            'pos.discount.apply',
            'pos.discount.override-limit',

            // Customers
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',

            // Sales
            'sales.view',
            'sales.view-all', // view-all = see other cashiers' sales, not just own
            'sales.cancel',
            'sales.refund',
            'sales.reprint',

            // Reports
            'reports.sales',
            'reports.profit',
            'reports.inventory',
            'reports.export',

            // Settings
            'settings.store',
            'settings.tax',
            'settings.currency',
            'settings.receipt',
            'settings.backup',
            'settings.restore',

            // Users & RBAC
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.manage',

            // Activity log
            'activity-logs.view',

            // POS sync review
            'pos-sync-audits.view',
            'pos-sync-audits.review',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions(array_diff($permissions, [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.manage',
            'settings.backup',
            'settings.restore',
        ]));

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'dashboard.view',
            'pos.access',
            'pos.discount.apply',
            'customers.view',
            'customers.create',
            'customers.update',
            'sales.view', // own sales only -- enforced in SalePolicy, not by permission name alone
            'sales.reprint',
        ]);

        $stockClerk = Role::firstOrCreate(['name' => 'stock_clerk', 'guard_name' => 'web']);
        $stockClerk->syncPermissions([
            'dashboard.view',
            'products.view',
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'purchases.view',
            'purchases.create',
            'purchases.update',
            'purchases.receive',
            'stock-adjustments.view',
            'stock-adjustments.create',
            'stock-movements.view',
            'reports.inventory',
        ]);

        Cache::forget(config('permission.cache.key') ?? 'spatie.permission.cache');
    }
}
