<?php

namespace App\Repositories\Eloquent;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentSupplierRepository extends BaseRepository
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paginate($perPage, [], function (Builder $query) use ($filters) {
            if (!empty($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }
            if (isset($filters['is_active'])) {
                $query->where('is_active', (bool) $filters['is_active']);
            }
            $query->orderBy('name');
        });
    }
}
