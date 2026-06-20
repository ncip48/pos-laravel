<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'is_guest',
        'store_credit_cents',
    ];

    protected function casts(): array
    {
        return [
            'is_guest' => 'boolean',
            'store_credit_cents' => 'integer',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function storeCredit(): Money
    {
        return Money::fromAmount($this->store_credit_cents);
    }

    public function totalSpent(): Money
    {
        return Money::fromAmount(
            (int) $this->sales()->whereIn('status', ['completed', 'partially_refunded'])->sum('total_cents')
        );
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * Walk-in/guest customer used as the default customer_id for POS sales
     * where no specific customer was selected. Memoized per-request since
     * this is looked up on every POS checkout.
     */
    public static function guest(): self
    {
        static $guest = null;

        return $guest ??= self::firstOrCreate(
            ['is_guest' => true],
            ['name' => 'Walk-in Customer']
        );
    }
}
