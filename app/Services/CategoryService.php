<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository) {}

    public function getByHousehold(int $householdId): Collection
    {
        return $this->categoryRepository->getByHousehold($householdId);
    }

    public function getByHouseholdGrouped(int $householdId): array
    {
        return $this->categoryRepository->getByHouseholdGrouped($householdId);
    }

    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        return $this->categoryRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $category = $this->categoryRepository->find($id);

        if ($category && $category->transactions()->exists()) {
            return false;
        }

        return $this->categoryRepository->delete($id);
    }

    public function seedDefaults(int $householdId): void
    {
        $defaults = [
            ['name' => 'Gaji', 'type' => 'income'],
            ['name' => 'Freelance', 'type' => 'income'],
            ['name' => 'Investasi', 'type' => 'income'],
            ['name' => 'Lainnya', 'type' => 'income'],
            ['name' => 'Makanan & Minuman', 'type' => 'expense'],
            ['name' => 'Transportasi', 'type' => 'expense'],
            ['name' => 'Belanja', 'type' => 'expense'],
            ['name' => 'Tagihan', 'type' => 'expense'],
            ['name' => 'Hiburan', 'type' => 'expense'],
            ['name' => 'Kesehatan', 'type' => 'expense'],
            ['name' => 'Pendidikan', 'type' => 'expense'],
            ['name' => 'Lainnya', 'type' => 'expense'],
        ];

        foreach ($defaults as $category) {
            $this->categoryRepository->create(array_merge($category, ['household_id' => $householdId]));
        }
    }
}
