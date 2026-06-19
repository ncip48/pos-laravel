<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function tree(): Collection
    {
        return $this->model->newQuery()
            ->rootOnly()
            ->with('children')
            ->orderBy('name')
            ->get();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->when($excludeId, fn(Builder $q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
