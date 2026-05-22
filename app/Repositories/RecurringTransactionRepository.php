<?php

namespace App\Repositories;

use App\Models\RecurringTransaction;
use App\Repositories\Contracts\RecurringTransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RecurringTransactionRepository extends BaseRepository implements RecurringTransactionRepositoryInterface
{
    public function __construct(RecurringTransaction $model)
    {
        parent::__construct($model);
    }

    public function getByHousehold(int $householdId): Collection
    {
        return $this->model
            ->where('household_id', $householdId)
            ->with(['account', 'category'])
            ->get();
    }

    public function getDueToday(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where('next_due_date', '<=', now()->toDateString())
            ->get();
    }
}
