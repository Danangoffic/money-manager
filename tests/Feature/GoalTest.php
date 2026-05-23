<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
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

    public function test_goals_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('goals.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Goals/Index'));
    }

    public function test_can_create_goal(): void
    {
        $response = $this->actingAs($this->user)->post(route('goals.store'), [
            'name' => 'Beli Motor',
            'target_amount' => 20000000,
            'current_amount' => 0,
            'deadline' => now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('goals.index'));
        $this->assertDatabaseHas('goals', [
            'household_id' => $this->household->id,
            'name' => 'Beli Motor',
            'target_amount' => 20000000,
            'current_amount' => 0,
        ]);
    }

    public function test_can_create_goal_without_deadline(): void
    {
        $response = $this->actingAs($this->user)->post(route('goals.store'), [
            'name' => 'Dana Darurat',
            'target_amount' => 50000000,
            'current_amount' => 5000000,
            'deadline' => null,
        ]);

        $response->assertRedirect(route('goals.index'));
        $this->assertDatabaseHas('goals', [
            'name' => 'Dana Darurat',
            'target_amount' => 50000000,
        ]);
    }

    public function test_can_update_goal(): void
    {
        $goal = Goal::factory()->create(['household_id' => $this->household->id]);

        $response = $this->actingAs($this->user)->put(route('goals.update', $goal->id), [
            'name' => 'Updated Goal',
            'target_amount' => 30000000,
        ]);

        $response->assertRedirect(route('goals.index'));
        $this->assertEquals('Updated Goal', $goal->fresh()->name);
        $this->assertEquals(30000000, $goal->fresh()->target_amount);
    }

    public function test_can_update_goal_progress(): void
    {
        $goal = Goal::factory()->create([
            'household_id' => $this->household->id,
            'target_amount' => 10000000,
            'current_amount' => 2000000,
        ]);

        $response = $this->actingAs($this->user)->patch(route('goals.update-progress', $goal->id), [
            'current_amount' => 5000000,
        ]);

        $response->assertRedirect();
        $this->assertEquals(5000000, $goal->fresh()->current_amount);
    }

    public function test_can_delete_goal(): void
    {
        $goal = Goal::factory()->create(['household_id' => $this->household->id]);

        $response = $this->actingAs($this->user)->delete(route('goals.destroy', $goal->id));

        $response->assertRedirect(route('goals.index'));
        $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
    }

    public function test_goal_requires_name(): void
    {
        $response = $this->actingAs($this->user)->post(route('goals.store'), [
            'target_amount' => 10000000,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_goal_requires_positive_target_amount(): void
    {
        $response = $this->actingAs($this->user)->post(route('goals.store'), [
            'name' => 'Test Goal',
            'target_amount' => 0,
        ]);

        $response->assertSessionHasErrors('target_amount');
    }

    public function test_goal_progress_cannot_be_negative(): void
    {
        $goal = Goal::factory()->create(['household_id' => $this->household->id]);

        $response = $this->actingAs($this->user)->patch(route('goals.update-progress', $goal->id), [
            'current_amount' => -1000,
        ]);

        $response->assertSessionHasErrors('current_amount');
    }

    public function test_goal_percentage_attribute_works(): void
    {
        $goal = Goal::factory()->create([
            'household_id' => $this->household->id,
            'target_amount' => 10000000,
            'current_amount' => 7500000,
        ]);

        $this->assertEquals(75.0, $goal->percentage);
    }

    public function test_goal_percentage_is_capped_at_100(): void
    {
        $goal = Goal::factory()->create([
            'household_id' => $this->household->id,
            'target_amount' => 10000000,
            'current_amount' => 15000000,
        ]);

        $this->assertEquals(100, $goal->percentage);
    }
}
