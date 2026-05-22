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

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Household $household;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->household = Household::factory()->create();
        HouseholdMember::create(['household_id' => $this->household->id, 'user_id' => $this->user->id, 'role' => 'admin']);
    }

    public function test_categories_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Categories/Index'));
    }

    public function test_can_create_expense_category(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Transportasi',
            'type' => 'expense',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'household_id' => $this->household->id,
            'name' => 'Transportasi',
            'type' => 'expense',
        ]);
    }

    public function test_can_create_income_category(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Freelance',
            'type' => 'income',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'household_id' => $this->household->id,
            'name' => 'Freelance',
            'type' => 'income',
        ]);
    }

    public function test_can_update_category(): void
    {
        $category = Category::factory()->create(['household_id' => $this->household->id]);

        $response = $this->actingAs($this->user)->put(route('categories.update', $category->id), [
            'name' => 'Updated Category',
            'type' => 'expense',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertEquals('Updated Category', $category->fresh()->name);
    }

    public function test_can_delete_category_without_transactions(): void
    {
        $category = Category::factory()->create(['household_id' => $this->household->id]);

        $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category->id));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_transactions(): void
    {
        $category = Category::factory()->expense()->create(['household_id' => $this->household->id]);
        $account = Account::factory()->create(['household_id' => $this->household->id]);

        Transaction::factory()->create([
            'household_id' => $this->household->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_requires_name(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'type' => 'expense',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_requires_valid_type(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Test',
            'type' => 'invalid',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_categories_are_scoped_to_household(): void
    {
        Category::factory()->create(['household_id' => $this->household->id, 'name' => 'My Category']);

        $otherHousehold = Household::factory()->create();
        Category::factory()->create(['household_id' => $otherHousehold->id, 'name' => 'Other Category']);

        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertOk();
    }
}
