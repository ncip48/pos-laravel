<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'barcode',
        'category_id',
        'unit_id',
        'description',
        'image_path',
        'cost_price_cents',
        'selling_price_cents',
        'tax_rate_percent',
        'is_tax_inclusive_price',
        'min_stock_level',
        'status',
        'track_stock',
    ];

    protected function casts(): array
    {
        return [
            'cost_price_cents' => 'integer',
            'selling_price_cents' => 'integer',
            'tax_rate_percent' => 'decimal:2',
            'is_tax_inclusive_price' => 'boolean',
            'min_stock_level' => 'integer',
            'track_stock' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // --- Money accessors --------------------------------------------------

    public function costPrice(): Money
    {
        return Money::fromCents($this->cost_price_cents);
    }

    public function sellingPrice(): Money
    {
        return Money::fromCents($this->selling_price_cents);
    }

    public function margin(): Money
    {
        return $this->sellingPrice()->subtract($this->costPrice());
    }

    // --- Stock read helpers -------------------------------------------------
    // These are READ-ONLY. Nothing here mutates stock_levels or writes a
    // stock_movements row — that is exclusively StockService's job. Models
    // stay free of business logic / transactional concerns by design.

    /**
     * Total stock across all warehouses. Prefer stockInWarehouse() when you
     * know the context (POS, a specific branch report) — this is for
     * dashboard-style aggregate views only, and triggers a query unless
     * stockLevels is already eager-loaded.
     */
    public function totalStock(): int
    {
        return $this->relationLoaded('stockLevels')
            ? $this->stockLevels->sum('quantity')
            : $this->stockLevels()->sum('quantity');
    }

    public function stockInWarehouse(int $warehouseId): int
    {
        if ($this->relationLoaded('stockLevels')) {
            return (int) $this->stockLevels
                ->firstWhere('warehouse_id', $warehouseId)?->quantity ?? 0;
        }

        return (int) $this->stockLevels()
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    public function isLowStock(int $warehouseId = null): bool
    {
        $quantity = $warehouseId ? $this->stockInWarehouse($warehouseId) : $this->totalStock();

        return $this->track_stock && $quantity <= $this->min_stock_level;
    }

    // --- Scopes -------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'barcode', 'cost_price_cents', 'selling_price_cents', 'status', 'min_stock_level'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('product');
    }
}
