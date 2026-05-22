<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AccountRepositoryInterface extends BaseRepositoryInterface
{
    public function getByHousehold(int $householdId): Collection;

    public function updateBalance(int $id, int $amount): void;
}
