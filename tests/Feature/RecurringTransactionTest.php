<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionTest extends TestCase
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

        $this->account = Account::factory()->create(['household_id' => $this->household->id]);
        $this->category = Category::factory()->expense()->create(['household_id' => $this->household->id]);
    }

    public function test_recurring_transactions_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('recurring-transactions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('RecurringTransactions/Index'));
    }

    public function test_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('recurring-transactions.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('RecurringTransactions/Create'));
    }

    public function test_can_create_recurring_transaction(): void
    {
        $response = $this->actingAs($this->user)->post(route('recurring-transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 150000,
            'description' => 'Internet Bulanan',
            'frequency' => 'monthly',
            'next_due_date' => '2025-02-01',
        ]);

        $response->assertRedirect(route('recurring-transactions.index'));
        $this->assertDatabaseHas('recurring_transactions', [
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 150000,
            'frequency' => 'monthly',
            'is_active' => true,
        ]);
    }

    public function test_can_toggle_recurring_transaction(): void
    {
        $recurring = RecurringTransaction::factory()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->patch(route('recurring-transactions.toggle', $recurring->id));

        $response->assertRedirect();
        $this->assertFalse($recurring->fresh()->is_active);
    }

    public function test_toggle_inactive_to_active(): void
    {
        $recurring = RecurringTransaction::factory()->inactive()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
        ]);

        $this->actingAs($this->user)->patch(route('recurring-transactions.toggle', $recurring->id));

        $this->assertTrue($recurring->fresh()->is_active);
    }

    public function test_can_delete_recurring_transaction(): void
    {
        $recurring = RecurringTransaction::factory()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('recurring-transactions.destroy', $recurring->id));

        $response->assertRedirect(route('recurring-transactions.index'));
        $this->assertDatabaseMissing('recurring_transactions', ['id' => $recurring->id]);
    }

    public function test_recurring_transaction_requires_valid_frequency(): void
    {
        $response = $this->actingAs($this->user)->post(route('recurring-transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 150000,
            'frequency' => 'invalid',
            'next_due_date' => '2025-02-01',
        ]);

        $response->assertSessionHasErrors('frequency');
    }

    public function test_recurring_transaction_requires_positive_amount(): void
    {
        $response = $this->actingAs($this->user)->post(route('recurring-transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 0,
            'frequency' => 'monthly',
            'next_due_date' => '2025-02-01',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_process_due_creates_transaction_and_advances_date(): void
    {
        $recurring = RecurringTransaction::factory()->monthly()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 200000,
            'next_due_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $service = app(\App\Services\RecurringTransactionService::class);
        $count = $service->processDue();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('transactions', [
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'amount' => 200000,
            'type' => 'expense',
        ]);

        $this->assertEquals(
            now()->addMonth()->toDateString(),
            $recurring->fresh()->next_due_date->toDateString()
        );
    }

    public function test_inactive_recurring_transaction_not_processed(): void
    {
        RecurringTransaction::factory()->inactive()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'next_due_date' => now()->toDateString(),
        ]);

        $service = app(\App\Services\RecurringTransactionService::class);
        $count = $service->processDue();

        $this->assertEquals(0, $count);
    }
}
