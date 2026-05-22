<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringTransaction\StoreRecurringTransactionRequest;
use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringTransactionController extends Controller
{
    public function __construct(
        private RecurringTransactionService $recurringService,
        private AccountService $accountService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('RecurringTransactions/Index', [
            'recurringTransactions' => $this->recurringService->getByHousehold($householdId),
        ]);
    }

    public function create(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('RecurringTransactions/Create', [
            'accounts' => $this->accountService->getByHousehold($householdId),
            'categories' => $this->categoryService->getByHouseholdGrouped($householdId),
        ]);
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $this->recurringService->create(array_merge(
            $request->validated(),
            ['household_id' => $request->user()->household_id]
        ));

        return redirect()->route('recurring-transactions.index');
    }

    public function toggle(int $id): RedirectResponse
    {
        $this->recurringService->toggle($id);

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->recurringService->delete($id);

        return redirect()->route('recurring-transactions.index');
    }
}
