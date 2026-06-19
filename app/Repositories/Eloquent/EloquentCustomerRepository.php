<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentCustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function searchForPos(string $term, int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->where('is_guest', false)
            ->search($term)
            ->limit($limit)
            ->get();
    }

    public function phoneExists(string $phone, ?int $excludeId = null): bool
    {
        return $this->model->newQuery()
            ->where('phone', $phone)
            ->when($excludeId, fn(Builder $q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function purchaseHistory(Customer $customer, int $perPage = 15)
    {
        return $customer->sales()
            ->with(['items', 'warehouse'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
