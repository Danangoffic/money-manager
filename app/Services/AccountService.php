<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AccountService
{
    public function __construct(private AccountRepositoryInterface $accountRepository) {}

    public function getByHousehold(int $householdId): Collection
    {
        return $this->accountRepository->getByHousehold($householdId);
    }

    public function getTotalBalance(int $householdId): int
    {
        return (int) $this->getByHousehold($householdId)->sum('balance');
    }

    public function create(array $data): Account
    {
        return $this->accountRepository->create($data);
    }

    public function update(int $id, array $data): Account
    {
        return $this->accountRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->accountRepository->delete($id);
    }
}
