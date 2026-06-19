<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Sample catalog data for local development only -- called conditionally
 * from DatabaseSeeder when app()->environment('local'). Never runs in
 * staging/production seeding. Useful for immediately exercising the POS
 * screen and dashboard without manually typing in products first.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::where('code', 'WH-01')->firstOrFail();
        $pcsUnit = Unit::where('symbol', 'pcs')->firstOrFail();

        $beverages = Category::firstOrCreate(['slug' => 'beverages'], ['name' => 'Beverages', 'is_active' => true]);
        $snacks = Category::firstOrCreate(['slug' => 'snacks'], ['name' => 'Snacks', 'is_active' => true]);

        $demoProducts = [
            ['name' => 'Bottled Water 600ml', 'category' => $beverages, 'cost' => 200, 'price' => 500, 'stock' => 120],
            ['name' => 'Instant Coffee Sachet', 'category' => $beverages, 'cost' => 150, 'price' => 350, 'stock' => 8],
            ['name' => 'Potato Chips 75g', 'category' => $snacks, 'cost' => 700, 'price' => 1200, 'stock' => 45],
            ['name' => 'Chocolate Bar', 'category' => $snacks, 'cost' => 400, 'price' => 900, 'stock' => 3],
        ];

        foreach ($demoProducts as $i => $data) {
            $product = Product::firstOrCreate(
                ['sku' => 'DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $data['name'],
                    'slug' => \Illuminate\Support\Str::slug($data['name']),
                    'barcode' => '890' . str_pad((string) ($i + 1), 10, '0', STR_PAD_LEFT),
                    'category_id' => $data['category']->id,
                    'unit_id' => $pcsUnit->id,
                    'cost_price_cents' => $data['cost'],
                    'selling_price_cents' => $data['price'],
                    'min_stock_level' => 10,
                    'status' => 'active',
                    'track_stock' => true,
                ]
            );

            StockLevel::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => $data['stock'], 'updated_at' => now()]
            );
        }
    }
}
