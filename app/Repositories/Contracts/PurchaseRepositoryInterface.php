<?php

namespace App\Repositories\Contracts;

interface PurchaseRepositoryInterface extends BaseRepositoryInterface
{
    public function nextPurchaseNumber(): string;

    public function paginateWithFilters(array $filters, int $perPage = 15);
}
