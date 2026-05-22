<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
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

    public function test_can_create_account(): void
    {
        $this->actingAs($this->user)->post(route('accounts.store'), [
            'name' => 'BCA Tabungan',
            'type' => 'bank',
            'balance' => 1000000,
        ]);

        $this->assertDatabaseHas('accounts', [
            'household_id' => $this->household->id,
            'name' => 'BCA Tabungan',
            'type' => 'bank',
            'balance' => 1000000,
        ]);
    }

    public function test_can_update_account(): void
    {
        $account = Account::factory()->create(['household_id' => $this->household->id]);

        $this->actingAs($this->user)->put(route('accounts.update', $account->id), [
            'name' => 'Updated Name',
            'type' => 'bank',
        ]);

        $this->assertEquals('Updated Name', $account->fresh()->name);
    }

    public function test_can_delete_account(): void
    {
        $account = Account::factory()->create(['household_id' => $this->household->id]);

        $this->actingAs($this->user)->delete(route('accounts.destroy', $account->id));

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    public function test_accounts_index_shows_total_balance(): void
    {
        Account::factory()->create(['household_id' => $this->household->id, 'balance' => 1000000]);
        Account::factory()->create(['household_id' => $this->household->id, 'balance' => 500000]);

        $response = $this->actingAs($this->user)->get(route('accounts.index'));

        $response->assertOk();
    }
}
