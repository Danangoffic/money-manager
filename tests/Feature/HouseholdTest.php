<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithHousehold(string $role = 'admin'): array
    {
        $user = User::factory()->create();
        $household = Household::factory()->create();
        HouseholdMember::create(['household_id' => $household->id, 'user_id' => $user->id, 'role' => $role]);

        return [$user, $household];
    }

    public function test_register_creates_household_for_user(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('household_members', ['user_id' => $user->id, 'role' => 'admin']);
    }

    public function test_admin_can_invite_member(): void
    {
        [$admin, $household] = $this->createUserWithHousehold('admin');
        $invitee = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('household.invite'), ['email' => $invitee->email]);

        $this->assertDatabaseHas('household_members', [
            'household_id' => $household->id,
            'user_id' => $invitee->id,
            'role' => 'member',
        ]);
    }

    public function test_member_cannot_invite_other_members(): void
    {
        [$member, $household] = $this->createUserWithHousehold('member');
        $otherUser = User::factory()->create();

        $response = $this->actingAs($member)
            ->post(route('household.invite'), ['email' => $otherUser->email]);

        $response->assertForbidden();
    }

    public function test_admin_can_remove_member(): void
    {
        [$admin, $household] = $this->createUserWithHousehold('admin');
        $member = User::factory()->create();
        HouseholdMember::create(['household_id' => $household->id, 'user_id' => $member->id, 'role' => 'member']);

        $this->actingAs($admin)
            ->delete(route('household.remove-member', $member->id));

        $this->assertDatabaseMissing('household_members', [
            'household_id' => $household->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_admin_can_change_member_role(): void
    {
        [$admin, $household] = $this->createUserWithHousehold('admin');
        $member = User::factory()->create();
        $memberRecord = HouseholdMember::create(['household_id' => $household->id, 'user_id' => $member->id, 'role' => 'member']);

        $this->actingAs($admin)
            ->patch(route('household.change-role', $memberRecord->id), ['role' => 'admin']);

        $this->assertDatabaseHas('household_members', ['id' => $memberRecord->id, 'role' => 'admin']);
    }

    public function test_user_without_household_is_redirected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('household.create'));
    }
}
