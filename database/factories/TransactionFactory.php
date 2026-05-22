<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'account_id' => Account::factory(),
            'category_id' => null,
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['income', 'expense']),
            'amount' => fake()->numberBetween(10000, 1000000),
            'description' => fake()->sentence(3),
            'date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'transfer_to_account_id' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(['type' => 'income']);
    }

    public function expense(): static
    {
        return $this->state(['type' => 'expense']);
    }
}
