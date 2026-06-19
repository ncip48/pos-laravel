<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-01'],
            ['name' => 'Main Store', 'is_active' => true, 'is_default' => true]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('change-this-password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Base units a small retail store typically needs out of the box.
        Unit::firstOrCreate(['symbol' => 'pcs'], ['name' => 'Piece', 'conversion_factor' => 1]);
        Unit::firstOrCreate(['symbol' => 'kg'], ['name' => 'Kilogram', 'conversion_factor' => 1]);
        Unit::firstOrCreate(['symbol' => 'box'], ['name' => 'Box', 'conversion_factor' => 1]);

        // Walk-in/guest customer used as the default for POS sales with no
        // specific customer selected -- see Customer::guest().
        Customer::firstOrCreate(['is_guest' => true], ['name' => 'Walk-in Customer']);

        if (app()->environment('local')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
