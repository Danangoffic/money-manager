<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface GoalRepositoryInterface extends BaseRepositoryInterface
{
    public function getByHousehold(int $householdId): Collection;
}
