<?php

namespace App\Repositories;

use App\Models\Goal;
use App\Repositories\Contracts\GoalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GoalRepository extends BaseRepository implements GoalRepositoryInterface
{
    public function __construct(Goal $model)
    {
        parent::__construct($model);
    }

    public function getByHousehold(int $householdId): Collection
    {
        return $this->model->where('household_id', $householdId)->get();
    }
}
