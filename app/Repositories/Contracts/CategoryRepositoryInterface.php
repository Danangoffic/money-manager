<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getByHousehold(int $householdId): Collection;

    public function getByHouseholdGrouped(int $householdId): array;
}
