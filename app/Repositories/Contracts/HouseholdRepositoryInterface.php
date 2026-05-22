<?php

namespace App\Repositories\Contracts;

use App\Models\Household;

interface HouseholdRepositoryInterface extends BaseRepositoryInterface
{
    public function findWithMembers(int $id): ?Household;
}
