<?php

namespace App\Services;

class DashboardService
{
    public function __construct(
        private AccountService $accountService,
        private TransactionService $transactionService,
        private BudgetService $budgetService,
        private GoalService $goalService,
    ) {}

    public function getSummary(int $householdId): array
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        return [
            'total_balance' => $this->accountService->getTotalBalance($householdId),
            'income_this_month' => $this->transactionService->sumByType($householdId, 'income', $startOfMonth, $endOfMonth),
            'expense_this_month' => $this->transactionService->sumByType($householdId, 'expense', $startOfMonth, $endOfMonth),
            'recent_transactions' => $this->transactionService->getRecentByHousehold($householdId),
            'budget_alerts' => $this->budgetService->getOverBudgetAlerts($householdId),
            'goals' => $this->goalService->getByHousehold($householdId),
        ];
    }
}
