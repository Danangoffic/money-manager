<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Household $household;

    private Account $account;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->household = Household::factory()->create();
        HouseholdMember::create(['household_id' => $this->household->id, 'user_id' => $this->user->id, 'role' => 'admin']);

        $this->account = Account::factory()->create(['household_id' => $this->household->id, 'balance' => 1000000]);
        $this->category = Category::factory()->expense()->create(['household_id' => $this->household->id]);
    }

    public function test_expense_transaction_decreases_account_balance(): void
    {
        $this->actingAs($this->user)->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 50000,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(950000, $this->account->fresh()->balance);
    }

    public function test_income_transaction_increases_account_balance(): void
    {
        $incomeCategory = Category::factory()->income()->create(['household_id' => $this->household->id]);

        $this->actingAs($this->user)->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 200000,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(1200000, $this->account->fresh()->balance);
    }

    public function test_deleting_transaction_restores_balance(): void
    {
        $transaction = Transaction::factory()->expense()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'amount' => 50000,
        ]);

        $this->account->update(['balance' => 950000]);

        $this->actingAs($this->user)->delete(route('transactions.destroy', $transaction->id));

        $this->assertEquals(1000000, $this->account->fresh()->balance);
    }

    public function test_transfer_debits_source_and_credits_destination(): void
    {
        $destination = Account::factory()->create(['household_id' => $this->household->id, 'balance' => 500000]);

        $this->actingAs($this->user)->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'type' => 'transfer',
            'amount' => 300000,
            'date' => now()->toDateString(),
            'transfer_to_account_id' => $destination->id,
        ]);

        $this->assertEquals(700000, $this->account->fresh()->balance);
        $this->assertEquals(800000, $destination->fresh()->balance);
    }

    public function test_transfer_to_same_account_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'type' => 'transfer',
            'amount' => 300000,
            'date' => now()->toDateString(),
            'transfer_to_account_id' => $this->account->id,
        ]);

        $response->assertSessionHasErrors('transfer_to_account_id');
    }

    public function test_transactions_index_shows_paginated_results(): void
    {
        Transaction::factory()->count(20)->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Transactions/Index'));
    }
}
