<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function tree(): Collection;

    public function slugExists(string $slug, ?int $excludeId = null): bool;
}
