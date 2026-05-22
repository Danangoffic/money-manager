<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        $household = Household::create(['name' => 'Demo Household']);

        HouseholdMember::create(['household_id' => $household->id, 'user_id' => $user->id, 'role' => 'admin']);

        // Accounts
        $cash = Account::create(['household_id' => $household->id, 'name' => 'Cash', 'type' => 'cash', 'balance' => 500000]);
        $bca = Account::create(['household_id' => $household->id, 'name' => 'BCA', 'type' => 'bank', 'balance' => 5000000]);

        // Categories
        $categories = [
            ['name' => 'Gaji', 'type' => 'income'],
            ['name' => 'Freelance', 'type' => 'income'],
            ['name' => 'Makanan & Minuman', 'type' => 'expense'],
            ['name' => 'Transportasi', 'type' => 'expense'],
            ['name' => 'Belanja', 'type' => 'expense'],
            ['name' => 'Tagihan', 'type' => 'expense'],
            ['name' => 'Hiburan', 'type' => 'expense'],
            ['name' => 'Kesehatan', 'type' => 'expense'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['name']] = Category::create(array_merge($cat, ['household_id' => $household->id]));
        }

        // Transactions (last 2 months)
        $sampleTransactions = [
            ['account_id' => $bca->id, 'category_id' => $createdCategories['Gaji']->id, 'type' => 'income', 'amount' => 8000000, 'description' => 'Gaji Mei', 'date' => now()->startOfMonth()->format('Y-m-d')],
            ['account_id' => $cash->id, 'category_id' => $createdCategories['Makanan & Minuman']->id, 'type' => 'expense', 'amount' => 50000, 'description' => 'Makan siang', 'date' => now()->format('Y-m-d')],
            ['account_id' => $cash->id, 'category_id' => $createdCategories['Transportasi']->id, 'type' => 'expense', 'amount' => 30000, 'description' => 'Ojek online', 'date' => now()->subDays(1)->format('Y-m-d')],
            ['account_id' => $bca->id, 'category_id' => $createdCategories['Tagihan']->id, 'type' => 'expense', 'amount' => 300000, 'description' => 'Listrik', 'date' => now()->subDays(3)->format('Y-m-d')],
            ['account_id' => $bca->id, 'category_id' => $createdCategories['Belanja']->id, 'type' => 'expense', 'amount' => 450000, 'description' => 'Belanja bulanan', 'date' => now()->subDays(5)->format('Y-m-d')],
        ];

        foreach ($sampleTransactions as $t) {
            Transaction::create(array_merge($t, ['household_id' => $household->id, 'user_id' => $user->id]));
        }

        // Budgets
        $month = now()->format('Y-m-01');
        Budget::create(['household_id' => $household->id, 'category_id' => $createdCategories['Makanan & Minuman']->id, 'amount' => 1500000, 'month' => $month]);
        Budget::create(['household_id' => $household->id, 'category_id' => $createdCategories['Transportasi']->id, 'amount' => 500000, 'month' => $month]);
        Budget::create(['household_id' => $household->id, 'category_id' => $createdCategories['Belanja']->id, 'amount' => 1000000, 'month' => $month]);

        // Goals
        Goal::create(['household_id' => $household->id, 'name' => 'Liburan Bali', 'target_amount' => 10000000, 'current_amount' => 3000000, 'deadline' => now()->addMonths(6)->format('Y-m-d')]);
        Goal::create(['household_id' => $household->id, 'name' => 'Dana Darurat', 'target_amount' => 30000000, 'current_amount' => 12000000, 'deadline' => null]);
    }
}
