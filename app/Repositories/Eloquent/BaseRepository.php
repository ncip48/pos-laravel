<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function all(array $with = []): Collection
    {
        return $this->model->newQuery()->with($with)->get();
    }

    public function paginate(int $perPage = 15, array $with = [], ?callable $queryModifier = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with($with);

        if ($queryModifier) {
            $queryModifier($query);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);

        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }
}
