<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Category;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Household $household;

    private Account $account;

    private Category $category;

    private string $token;

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

        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_can_list_transactions(): void
    {
        Transaction::factory()->count(3)->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/transactions');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_create_transaction(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/transactions', [
                'account_id' => $this->account->id,
                'category_id' => $this->category->id,
                'type' => 'expense',
                'amount' => 250000,
                'description' => 'API Test Transaction',
                'date' => now()->toDateString(),
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.amount', 250000);
        $response->assertJsonPath('data.type', 'expense');
    }

    public function test_can_show_single_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/v1/transactions/{$transaction->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $transaction->id);
    }

    public function test_can_update_transaction(): void
    {
        $transaction = Transaction::factory()->expense()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'amount' => 100000,
        ]);

        // Deduct balance to simulate original creation
        $this->account->update(['balance' => 4900000]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/v1/transactions/{$transaction->id}", [
                'account_id' => $this->account->id,
                'category_id' => $this->category->id,
                'type' => 'expense',
                'amount' => 200000,
                'date' => now()->toDateString(),
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.amount', 200000);
    }

    public function test_can_delete_transaction(): void
    {
        $transaction = Transaction::factory()->expense()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'amount' => 100000,
        ]);

        $this->account->update(['balance' => 4900000]);

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/v1/transactions/{$transaction->id}");

        $response->assertOk();
        $response->assertJson(['message' => 'Transaksi berhasil dihapus.']);
    }

    public function test_create_requires_valid_data(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/transactions', [
                'type' => 'invalid',
                'amount' => -100,
            ]);

        $response->assertUnprocessable();
    }

    public function test_cannot_access_without_token(): void
    {
        $response = $this->getJson('/api/v1/transactions');

        $response->assertUnauthorized();
    }

    public function test_transactions_filtered_by_type(): void
    {
        Transaction::factory()->income()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'user_id' => $this->user->id,
        ]);
        Transaction::factory()->expense()->create([
            'household_id' => $this->household->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/transactions?type=income');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }
}
