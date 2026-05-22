<?php

namespace App\Repositories;

use App\Models\Household;
use App\Repositories\Contracts\HouseholdRepositoryInterface;

class HouseholdRepository extends BaseRepository implements HouseholdRepositoryInterface
{
    public function __construct(Household $model)
    {
        parent::__construct($model);
    }

    public function findWithMembers(int $id): ?Household
    {
        return $this->model->with('members')->find($id);
    }
}
