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

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Household $household;

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
    }

    public function test_reports_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Reports/Index'));
    }

    public function test_reports_accept_date_range_parameters(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]));

        $response->assertOk();
    }

    public function test_reports_show_expense_by_category_data(): void
    {
        $account = Account::factory()->create(['household_id' => $this->household->id]);
        $category = Category::factory()->expense()->create(['household_id' => $this->household->id]);

        Transaction::factory()->expense()->create([
            'household_id' => $this->household->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 500000,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->has('expenseByCategory')
            ->has('incomeVsExpense')
            ->has('cashFlow')
        );
    }

    public function test_reports_require_authentication(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }
}
