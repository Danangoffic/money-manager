<?php

namespace Database\Factories;

use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'name' => fake()->randomElement(['BCA', 'Mandiri', 'BNI', 'Cash', 'OVO', 'GoPay']),
            'type' => fake()->randomElement(['cash', 'bank', 'e-wallet', 'credit-card']),
            'balance' => fake()->numberBetween(0, 10000000),
            'icon' => null,
        ];
    }
}
