<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function searchForPos(string $term, int $limit = 10): Collection;

    public function phoneExists(string $phone, ?int $excludeId = null): bool;

    public function purchaseHistory(Customer $customer, int $perPage = 15);
}
