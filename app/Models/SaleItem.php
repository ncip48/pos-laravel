<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name_snapshot',
        'product_sku_snapshot',
        'quantity',
        'unit_price_cents',
        'unit_cost_cents',
        'discount_cents',
        'discount_type',
        'discount_value',
        'tax_cents',
        'tax_rate_percent',
        'subtotal_cents',
        'total_cents',
        'refunded_quantity',
        'current_price_at_sync_cents',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
            'unit_cost_cents' => 'integer',
            'discount_cents' => 'integer',
            'discount_value' => 'decimal:2',
            'tax_cents' => 'integer',
            'tax_rate_percent' => 'decimal:2',
            'subtotal_cents' => 'integer',
            'total_cents' => 'integer',
            'refunded_quantity' => 'integer',
            'current_price_at_sync_cents' => 'integer',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function refundItems(): HasMany
    {
        return $this->hasMany(SaleRefundItem::class);
    }

    public function unitPrice(): Money
    {
        return Money::fromAmount($this->unit_price_cents);
    }

    public function unitCost(): Money
    {
        return Money::fromAmount($this->unit_cost_cents);
    }

    /** Per-unit profit, using the price/cost LOCKED at sale time — never recomputed from the live catalog. */
    public function unitMargin(): Money
    {
        return $this->unitPrice()->subtract($this->unitCost());
    }

    public function lineMargin(): Money
    {
        return $this->unitMargin()->multiply($this->quantity);
    }

    public function quantityRefundable(): int
    {
        return max(0, $this->quantity - $this->refunded_quantity);
    }

    public function hasPriceDeviation(): bool
    {
        return $this->current_price_at_sync_cents !== null
            && $this->current_price_at_sync_cents !== $this->unit_price_cents;
    }
}
