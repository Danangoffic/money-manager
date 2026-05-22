<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private AccountRepositoryInterface $accountRepository,
    ) {}

    public function getByHouseholdFiltered(int $householdId, array $filters = []): LengthAwarePaginator
    {
        return $this->transactionRepository->getByHouseholdFiltered($householdId, $filters);
    }

    public function create(array $data): Transaction
    {
        $transaction = $this->transactionRepository->create($data);
        $this->applyBalanceChange($transaction);

        return $transaction;
    }

    public function update(int $id, array $data): Transaction
    {
        $old = $this->transactionRepository->find($id);
        $this->reverseBalanceChange($old);

        $transaction = $this->transactionRepository->update($id, $data);
        $this->applyBalanceChange($transaction);

        return $transaction;
    }

    public function delete(int $id): bool
    {
        $transaction = $this->transactionRepository->find($id);
        $this->reverseBalanceChange($transaction);

        return $this->transactionRepository->delete($id);
    }

    public function getRecentByHousehold(int $householdId, int $limit = 5): Collection
    {
        return $this->transactionRepository->getRecentByHousehold($householdId, $limit);
    }

    public function sumByType(int $householdId, string $type, string $startDate, string $endDate): int
    {
        return $this->transactionRepository->sumByType($householdId, $type, $startDate, $endDate);
    }

    public function sumByCategoryForPeriod(int $householdId, string $startDate, string $endDate): Collection
    {
        return $this->transactionRepository->sumByCategoryForPeriod($householdId, $startDate, $endDate);
    }

    public function sumByMonthForRange(int $householdId, string $startDate, string $endDate): Collection
    {
        return $this->transactionRepository->sumByMonthForRange($householdId, $startDate, $endDate);
    }

    public function countByPeriod(int $householdId, string $startDate, string $endDate): int
    {
        return $this->transactionRepository->countByPeriod($householdId, $startDate, $endDate);
    }

    private function applyBalanceChange(Transaction $transaction): void
    {
        match ($transaction->type) {
            'income' => $this->accountRepository->updateBalance($transaction->account_id, $transaction->amount),
            'expense' => $this->accountRepository->updateBalance($transaction->account_id, -$transaction->amount),
            'transfer' => $this->applyTransfer($transaction),
        };
    }

    private function reverseBalanceChange(Transaction $transaction): void
    {
        match ($transaction->type) {
            'income' => $this->accountRepository->updateBalance($transaction->account_id, -$transaction->amount),
            'expense' => $this->accountRepository->updateBalance($transaction->account_id, $transaction->amount),
            'transfer' => $this->reverseTransfer($transaction),
        };
    }

    private function applyTransfer(Transaction $transaction): void
    {
        $this->accountRepository->updateBalance($transaction->account_id, -$transaction->amount);
        $this->accountRepository->updateBalance($transaction->transfer_to_account_id, $transaction->amount);
    }

    private function reverseTransfer(Transaction $transaction): void
    {
        $this->accountRepository->updateBalance($transaction->account_id, $transaction->amount);
        $this->accountRepository->updateBalance($transaction->transfer_to_account_id, -$transaction->amount);
    }
}
