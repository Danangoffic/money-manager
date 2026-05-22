<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface BudgetRepositoryInterface extends BaseRepositoryInterface
{
    public function getByHouseholdAndMonth(int $householdId, string $month): Collection;

    public function updateOrCreate(array $attributes, array $values): \App\Models\Budget;
}
