<?php

namespace App\Http\Controllers;

use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Services\BudgetService;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetService $budgetService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;
        $month = $request->get('month', now()->format('Y-m'));

        return Inertia::render('Budgets/Index', [
            'budgets' => $this->budgetService->getMonthlyOverview($householdId, $month),
            'categories' => $this->categoryService->getByHousehold($householdId)->where('type', 'expense')->values(),
            'month' => $month,
            'isAdmin' => $request->user()->isHouseholdAdmin(),
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $this->budgetService->createOrUpdate(array_merge(
            $request->validated(),
            ['household_id' => $request->user()->household_id]
        ));

        return back()->with('success', 'Budget berhasil disimpan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->budgetService->delete($id);

        return back()->with('success', 'Budget berhasil dihapus.');
    }
}
