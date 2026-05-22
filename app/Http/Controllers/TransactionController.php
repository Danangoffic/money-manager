<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private AccountService $accountService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('Transactions/Index', [
            'transactions' => $this->transactionService->getByHouseholdFiltered($householdId, $request->all()),
            'accounts' => $this->accountService->getByHousehold($householdId),
            'categories' => $this->categoryService->getByHousehold($householdId),
            'filters' => $request->only(['type', 'account_id', 'category_id', 'start_date', 'end_date']),
        ]);
    }

    public function create(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('Transactions/Create', [
            'accounts' => $this->accountService->getByHousehold($householdId),
            'categories' => $this->categoryService->getByHouseholdGrouped($householdId),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->transactionService->create(array_merge(
            $request->validated(),
            [
                'household_id' => $request->user()->household_id,
                'user_id' => $request->user()->id,
            ]
        ));

        return redirect()->route('transactions.index');
    }

    public function edit(Request $request, int $id): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('Transactions/Edit', [
            'transaction' => \App\Models\Transaction::with(['account', 'category', 'transferToAccount'])->findOrFail($id),
            'accounts' => $this->accountService->getByHousehold($householdId),
            'categories' => $this->categoryService->getByHouseholdGrouped($householdId),
        ]);
    }

    public function update(StoreTransactionRequest $request, int $id): RedirectResponse
    {
        $this->transactionService->update($id, array_merge(
            $request->validated(),
            [
                'household_id' => $request->user()->household_id,
                'user_id' => $request->user()->id,
            ]
        ));

        return redirect()->route('transactions.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->transactionService->delete($id);

        return redirect()->route('transactions.index');
    }
}
