<?php

namespace App\Models;

use App\Enums\SaleStatus;
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

class Sale extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_number',
        'client_uuid',
        'customer_id',
        'warehouse_id',
        'register_id',
        'user_id',
        'status',
        'subtotal_cents',
        'discount_cents',
        'discount_type',
        'discount_value',
        'tax_cents',
        'tax_rate_percent',
        'total_cents',
        'paid_cents',
        'change_cents',
        'was_created_offline',
        'created_offline_at',
        'synced_at',
        'has_price_deviation',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SaleStatus::class,
            'subtotal_cents' => 'integer',
            'discount_cents' => 'integer',
            'discount_value' => 'decimal:2',
            'tax_cents' => 'integer',
            'tax_rate_percent' => 'decimal:2',
            'total_cents' => 'integer',
            'paid_cents' => 'integer',
            'change_cents' => 'integer',
            'was_created_offline' => 'boolean',
            'created_offline_at' => 'datetime',
            'synced_at' => 'datetime',
            'has_price_deviation' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(Register::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(SaleRefund::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function syncAudits(): HasMany
    {
        return $this->hasMany(PosSyncAudit::class);
    }

    public function total(): Money
    {
        return Money::fromAmount($this->total_cents);
    }

    public function balanceDue(): Money
    {
        return Money::fromAmount(max(0, $this->total_cents - $this->paid_cents));
    }

    public function isFullyRefundable(): bool
    {
        return $this->status === SaleStatus::Completed;
    }

    public function totalRefunded(): Money
    {
        return Money::fromAmount((int) $this->refunds()->sum('amount_cents'));
    }

    public function scopeStatus(Builder $query, SaleStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeForWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeBetweenDates(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeNeedsDeviationReview(Builder $query): Builder
    {
        return $query->where('has_price_deviation', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_cents', 'paid_cents'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('sale');
    }
}
