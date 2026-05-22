<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;
        $startDate = $request->get('start_date', now()->subMonths(5)->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        return Inertia::render('Reports/Index', [
            'expenseByCategory' => $this->reportService->getExpenseByCategory($householdId, $startDate, $endDate),
            'incomeVsExpense' => $this->reportService->getIncomeVsExpense($householdId, $startDate, $endDate),
            'cashFlow' => $this->reportService->getCashFlow($householdId, $startDate, $endDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
