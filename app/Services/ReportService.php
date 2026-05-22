<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    public function __construct(private TransactionService $transactionService) {}

    public function getExpenseByCategory(int $householdId, string $startDate, string $endDate): Collection
    {
        return $this->transactionService->sumByCategoryForPeriod($householdId, $startDate, $endDate);
    }

    public function getIncomeVsExpense(int $householdId, string $startDate, string $endDate): Collection
    {
        return $this->transactionService->sumByMonthForRange($householdId, $startDate, $endDate);
    }

    public function getCashFlow(int $householdId, string $startDate, string $endDate): array
    {
        $data = $this->transactionService->sumByMonthForRange($householdId, $startDate, $endDate);
        $grouped = $data->groupBy('month');

        return $grouped->map(function ($items, $month) {
            $income = $items->where('type', 'income')->sum('total');
            $expense = $items->where('type', 'expense')->sum('total');

            return [
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        })->values()->toArray();
    }
}
