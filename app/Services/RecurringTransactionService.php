<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Repositories\Contracts\RecurringTransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RecurringTransactionService
{
    public function __construct(
        private RecurringTransactionRepositoryInterface $recurringRepository,
        private TransactionService $transactionService,
    ) {}

    public function getByHousehold(int $householdId): Collection
    {
        return $this->recurringRepository->getByHousehold($householdId);
    }

    public function create(array $data): RecurringTransaction
    {
        return $this->recurringRepository->create($data);
    }

    public function update(int $id, array $data): RecurringTransaction
    {
        return $this->recurringRepository->update($id, $data);
    }

    public function toggle(int $id): RecurringTransaction
    {
        $recurring = $this->recurringRepository->find($id);
        $recurring->update(['is_active' => ! $recurring->is_active]);

        return $recurring->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->recurringRepository->delete($id);
    }

    public function processDue(): int
    {
        $dueItems = $this->recurringRepository->getDueToday();
        $count = 0;

        foreach ($dueItems as $recurring) {
            $this->transactionService->create([
                'household_id' => $recurring->household_id,
                'account_id' => $recurring->account_id,
                'category_id' => $recurring->category_id,
                'user_id' => $recurring->household->members()->first()->id,
                'type' => $recurring->type,
                'amount' => $recurring->amount,
                'description' => $recurring->description,
                'date' => now()->toDateString(),
            ]);

            $recurring->update(['next_due_date' => $this->calculateNextDueDate($recurring)]);
            $count++;
        }

        return $count;
    }

    private function calculateNextDueDate(RecurringTransaction $recurring): string
    {
        $date = $recurring->next_due_date;

        return match ($recurring->frequency) {
            'daily' => $date->addDay()->toDateString(),
            'weekly' => $date->addWeek()->toDateString(),
            'monthly' => $date->addMonth()->toDateString(),
            'yearly' => $date->addYear()->toDateString(),
        };
    }
}
