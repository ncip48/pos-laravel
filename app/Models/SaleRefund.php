<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'user_id',
        'reason',
        'amount_cents',
        'refund_method',
    ];

    protected function casts(): array
    {
        return ['amount_cents' => 'integer'];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleRefundItem::class);
    }

    public function amount(): Money
    {
        return Money::fromAmount($this->amount_cents);
    }
}
