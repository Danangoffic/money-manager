<?php

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository extends BaseRepository implements AccountRepositoryInterface
{
    public function __construct(Account $model)
    {
        parent::__construct($model);
    }

    public function getByHousehold(int $householdId): Collection
    {
        return $this->model->where('household_id', $householdId)->get();
    }

    public function updateBalance(int $id, int $amount): void
    {
        $this->model->where('id', $id)->increment('balance', $amount);
    }
}
