<?php

namespace App\Repositories\Eloquent;

use App\Models\Unit;

/**
 * Units and Suppliers currently need nothing beyond the base CRUD contract,
 * so — to avoid interface bloat for the sake of it — they're bound directly
 * as concrete classes in RepositoryServiceProvider rather than getting a
 * dedicated Contracts\UnitRepositoryInterface. If domain-specific queries
 * are needed later (e.g. "units used by product X"), extract an interface
 * then; until there's a second implementation or a testing need to swap
 * one out, the extra indirection doesn't pay for itself.
 */
class EloquentUnitRepository extends BaseRepository
{
    public function __construct(Unit $model)
    {
        parent::__construct($model);
    }
}
