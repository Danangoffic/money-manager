<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
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
        HouseholdMember::create([
            'household_id' => $this->household->id,
            'user_id' => $this->user->id,
            'role' => 'admin',
        ]);

        $this->account = Account::factory()->create([
            'household_id' => $this->household->id,
            'balance' => 5000000,
        ]);
        $this->category = Category::factory()->expense()->create([
            'household_id' => $this->household->id,
        ]);
    }

    public function test_creating_transaction_logs_activity(): void
    {
        $this->actingAs($this->user)->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 100000,
            'date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'household_id' => $this->household->id,
            'user_id' => $this->user->id,
            'action' => 'created',
            'model_type' => 'App\\Models\\Transaction',
        ]);
    }

    public function test_deleting_transaction_logs_activity(): void
    {
        // Create transaction first
        $this->actingAs($this->user)->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 100000,
            'date' => now()->toDateString(),
        ]);

        $transaction = \App\Models\Transaction::first();

        $this->actingAs($this->user)->delete(
            route('transactions.destroy', $transaction->id)
        );

        $this->assertDatabaseHas('activity_logs', [
            'household_id' => $this->household->id,
            'user_id' => $this->user->id,
            'action' => 'deleted',
            'model_type' => 'App\\Models\\Transaction',
            'model_id' => $transaction->id,
        ]);
    }

    public function test_activity_log_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('activity-logs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->component('ActivityLogs/Index')
        );
    }

    public function test_creating_account_logs_activity(): void
    {
        $this->actingAs($this->user)->post(route('accounts.store'), [
            'name' => 'Test Account',
            'type' => 'bank',
            'balance' => 1000000,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'household_id' => $this->household->id,
            'user_id' => $this->user->id,
            'action' => 'created',
            'model_type' => 'App\\Models\\Account',
        ]);
    }
}
