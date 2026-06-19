<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Common CRUD contract shared by every repository. Aggregate-specific
 * repositories (ProductRepositoryInterface, SaleRepositoryInterface, ...)
 * extend this and add domain-specific query methods (search, low-stock
 * filters, date-range scoping, etc).
 *
 * Deliberately NOT included here: anything that mutates state across
 * multiple tables, wraps a DB transaction, or fires events. That belongs in
 * a Service. A repository's find/create/update/delete operate on exactly
 * one aggregate root.
 */
interface BaseRepositoryInterface
{
    public function find(int $id): ?Model;

    public function findOrFail(int $id): Model;

    public function all(array $with = []): Collection;

    public function paginate(int $perPage = 15, array $with = [], ?callable $queryModifier = null): LengthAwarePaginator;

    public function create(array $attributes): Model;

    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;

    public function query(): Builder;
}
