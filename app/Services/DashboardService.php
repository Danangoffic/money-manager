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

        // 6 months range for chart
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth()->toDateString();

        // Last month for net worth comparison
        $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();

        $totalBalance = $this->accountService->getTotalBalance($householdId);
        $incomeThisMonth = $this->transactionService->sumByType($householdId, 'income', $startOfMonth, $endOfMonth);
        $expenseThisMonth = $this->transactionService->sumByType($householdId, 'expense', $startOfMonth, $endOfMonth);

        // Net worth change calculation
        $incomeLastMonth = $this->transactionService->sumByType($householdId, 'income', $lastMonthStart, $lastMonthEnd);
        $expenseLastMonth = $this->transactionService->sumByType($householdId, 'expense', $lastMonthStart, $lastMonthEnd);
        $netThisMonth = $incomeThisMonth - $expenseThisMonth;
        $netLastMonth = $incomeLastMonth - $expenseLastMonth;

        // Daily average expense
        $dayOfMonth = now()->day;
        $dailyAverageExpense = $dayOfMonth > 0 ? (int) round($expenseThisMonth / $dayOfMonth) : 0;

        return [
            'total_balance' => $totalBalance,
            'income_this_month' => $incomeThisMonth,
            'expense_this_month' => $expenseThisMonth,
            'recent_transactions' => $this->transactionService->getRecentByHousehold($householdId),
            'budget_alerts' => $this->budgetService->getOverBudgetAlerts($householdId),
            'goals' => $this->goalService->getByHousehold($householdId),

            // Chart data: 6 months income vs expense
            'monthly_chart' => $this->transactionService->sumByMonthForRange($householdId, $sixMonthsAgo, $endOfMonth),

            // Top expense categories this month
            'top_categories' => $this->transactionService->sumByCategoryForPeriod($householdId, $startOfMonth, $endOfMonth),

            // Net worth change
            'net_worth' => [
                'total' => $totalBalance,
                'net_this_month' => $netThisMonth,
                'net_last_month' => $netLastMonth,
                'change' => $netLastMonth !== 0
                    ? round((($netThisMonth - $netLastMonth) / abs($netLastMonth)) * 100, 1)
                    : ($netThisMonth > 0 ? 100 : 0),
            ],

            // Extra stats
            'daily_average_expense' => $dailyAverageExpense,
            'transaction_count' => $this->transactionService->countByPeriod($householdId, $startOfMonth, $endOfMonth),
        ];
    }
}
