<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('Accounts/Index', [
            'accounts' => $this->accountService->getByHousehold($householdId),
            'totalBalance' => $this->accountService->getTotalBalance($householdId),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->accountService->create(array_merge(
            $request->validated(),
            ['household_id' => $request->user()->household_id]
        ));

        return redirect()->route('accounts.index');
    }

    public function update(UpdateAccountRequest $request, int $id): RedirectResponse
    {
        $this->accountService->update($id, $request->validated());

        return redirect()->route('accounts.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->accountService->delete($id);

        return redirect()->route('accounts.index');
    }
}
