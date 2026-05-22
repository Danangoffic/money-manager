<?php

namespace Database\Factories;

use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    public function definition(): array
    {
        $target = fake()->numberBetween(1000000, 50000000);

        return [
            'household_id' => Household::factory(),
            'name' => fake()->words(3, true),
            'target_amount' => $target,
            'current_amount' => fake()->numberBetween(0, $target),
            'deadline' => fake()->optional()->dateTimeBetween('+1 month', '+2 years')?->format('Y-m-d'),
        ];
    }
}
