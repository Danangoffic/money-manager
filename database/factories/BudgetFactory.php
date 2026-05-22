<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->numberBetween(100000, 5000000),
            'month' => now()->format('Y-m') . '-01',
        ];
    }
}
