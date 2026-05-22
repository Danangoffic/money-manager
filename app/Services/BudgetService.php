<?php

namespace App\Services;

use App\Models\Budget;
use App\Repositories\Contracts\BudgetRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class BudgetService
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        private TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function getMonthlyOverview(int $householdId, string $month): array
    {
        $budgets = $this->budgetRepository->getByHouseholdAndMonth($householdId, $month);
        $startDate = $month.'-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $spent = $this->transactionRepository->sumByCategoryForPeriod($householdId, $startDate, $endDate);
        $spentMap = $spent->pluck('total', 'category_id');

        return $budgets->map(function (Budget $budget) use ($spentMap) {
            $spentAmount = $spentMap->get($budget->category_id, 0);

            return [
                'id' => $budget->id,
                'category' => $budget->category,
                'amount' => $budget->amount,
                'spent' => $spentAmount,
                'percentage' => $budget->amount > 0 ? min(100, round(($spentAmount / $budget->amount) * 100, 1)) : 0,
            ];
        })->toArray();
    }

    public function createOrUpdate(array $data): Budget
    {
        return $this->budgetRepository->updateOrCreate(
            [
                'household_id' => $data['household_id'],
                'category_id' => $data['category_id'],
                'month' => $data['month'],
            ],
            ['amount' => $data['amount']]
        );
    }

    public function delete(int $id): bool
    {
        return $this->budgetRepository->delete($id);
    }

    public function getOverBudgetAlerts(int $householdId): array
    {
        $month = now()->format('Y-m');
        $overview = $this->getMonthlyOverview($householdId, $month);

        return array_filter($overview, fn ($item) => $item['percentage'] >= 80);
    }
}
