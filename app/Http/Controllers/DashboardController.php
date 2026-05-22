<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private AccountService $accountService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('Dashboard', [
            'summary' => $this->dashboardService->getSummary($householdId),
            'accounts' => $this->accountService->getByHousehold($householdId),
            'categories' => $this->categoryService->getByHouseholdGrouped($householdId),
        ]);
    }
}
