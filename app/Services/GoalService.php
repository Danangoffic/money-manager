<?php

namespace App\Services;

use App\Models\Goal;
use App\Repositories\Contracts\GoalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GoalService
{
    public function __construct(private GoalRepositoryInterface $goalRepository) {}

    public function getByHousehold(int $householdId): Collection
    {
        return $this->goalRepository->getByHousehold($householdId);
    }

    public function create(array $data): Goal
    {
        return $this->goalRepository->create($data);
    }

    public function update(int $id, array $data): Goal
    {
        return $this->goalRepository->update($id, $data);
    }

    public function updateProgress(int $id, int $amount): Goal
    {
        $goal = $this->goalRepository->find($id);
        $goal->update(['current_amount' => $amount]);

        return $goal->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->goalRepository->delete($id);
    }
}
