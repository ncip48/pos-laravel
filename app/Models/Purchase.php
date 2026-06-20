<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'status',
        'order_date',
        'expected_date',
        'received_at',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'total_cents',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseStatus::class,
            'order_date' => 'date',
            'expected_date' => 'date',
            'received_at' => 'datetime',
            'subtotal_cents' => 'integer',
            'discount_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function total(): Money
    {
        return Money::fromAmount($this->total_cents);
    }

    public function isFullyReceived(): bool
    {
        return $this->items->every(fn(PurchaseItem $item) => $item->quantity_received >= $item->quantity_ordered);
    }

    public function scopeStatus(Builder $query, PurchaseStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_cents', 'received_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('purchase');
    }
}
