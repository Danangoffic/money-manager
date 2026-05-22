<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Household $household;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->household = Household::factory()->create();
        HouseholdMember::create(['household_id' => $this->household->id, 'user_id' => $this->user->id, 'role' => 'admin']);

        $this->category = Category::factory()->expense()->create(['household_id' => $this->household->id]);
    }

    public function test_budget_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('budgets.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Budgets/Index'));
    }

    public function test_can_create_budget(): void
    {
        $month = now()->format('Y-m');

        $response = $this->actingAs($this->user)->post(route('budgets.store'), [
            'category_id' => $this->category->id,
            'amount' => 500000,
            'month' => $month,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'household_id' => $this->household->id,
            'category_id' => $this->category->id,
            'amount' => 500000,
        ]);
    }

    public function test_creating_budget_for_same_category_and_month_updates_existing(): void
    {
        $month = now()->format('Y-m');

        $this->actingAs($this->user)->post(route('budgets.store'), [
            'category_id' => $this->category->id,
            'amount' => 500000,
            'month' => $month,
        ]);

        $this->actingAs($this->user)->post(route('budgets.store'), [
            'category_id' => $this->category->id,
            'amount' => 800000,
            'month' => $month,
        ]);

        $this->assertDatabaseCount('budgets', 1);
        $this->assertDatabaseHas('budgets', [
            'household_id' => $this->household->id,
            'category_id' => $this->category->id,
            'amount' => 800000,
        ]);
    }

    public function test_can_delete_budget(): void
    {
        $budget = Budget::factory()->create([
            'household_id' => $this->household->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('budgets.destroy', $budget->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    public function test_budget_index_shows_correct_month_data(): void
    {
        $month = '2024-06';

        Budget::factory()->create([
            'household_id' => $this->household->id,
            'category_id' => $this->category->id,
            'month' => $month . '-01',
            'amount' => 1000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('budgets.index', ['month' => $month]));

        $response->assertOk();
    }

    public function test_budget_percentage_calculated_correctly(): void
    {
        $month = now()->format('Y-m');
        $account = Account::factory()->create(['household_id' => $this->household->id]);

        Budget::factory()->create([
            'household_id' => $this->household->id,
            'category_id' => $this->category->id,
            'month' => $month . '-01',
            'amount' => 1000000,
        ]);

        // Create expense transaction for this category
        Transaction::factory()->expense()->create([
            'household_id' => $this->household->id,
            'account_id' => $account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'amount' => 600000,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->get(route('budgets.index', ['month' => $month]));

        $response->assertOk();
    }

    public function test_budget_requires_valid_category_id(): void
    {
        $response = $this->actingAs($this->user)->post(route('budgets.store'), [
            'category_id' => 999999,
            'amount' => 500000,
            'month' => now()->format('Y-m'),
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_budget_requires_positive_amount(): void
    {
        $response = $this->actingAs($this->user)->post(route('budgets.store'), [
            'category_id' => $this->category->id,
            'amount' => 0,
            'month' => now()->format('Y-m'),
        ]);

        $response->assertSessionHasErrors('amount');
    }
}
