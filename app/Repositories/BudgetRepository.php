<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Repositories\Contracts\BudgetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository extends BaseRepository implements BudgetRepositoryInterface
{
    public function __construct(Budget $model)
    {
        parent::__construct($model);
    }

    public function getByHouseholdAndMonth(int $householdId, string $month): Collection
    {
        return $this->model
            ->where('household_id', $householdId)
            ->where('month', $month)
            ->with('category')
            ->get();
    }

    public function updateOrCreate(array $attributes, array $values): Budget
    {
        return $this->model->updateOrCreate($attributes, $values);
    }
}
