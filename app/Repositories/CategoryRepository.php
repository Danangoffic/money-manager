<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getByHousehold(int $householdId): Collection
    {
        return $this->model->where('household_id', $householdId)->get();
    }

    public function getByHouseholdGrouped(int $householdId): array
    {
        $categories = $this->getByHousehold($householdId);

        return [
            'income' => $categories->where('type', 'income')->values(),
            'expense' => $categories->where('type', 'expense')->values(),
        ];
    }
}
