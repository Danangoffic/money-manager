<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface RecurringTransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function getByHousehold(int $householdId): Collection;

    public function getDueToday(): Collection;
}
