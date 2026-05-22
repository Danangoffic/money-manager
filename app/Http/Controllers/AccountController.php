<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Services\AccountService;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService,
        private ActivityLogService $activityLogService,
    ) {}

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
        $account = $this->accountService->create(array_merge(
            $request->validated(),
            ['household_id' => $request->user()->household_id]
        ));

        $this->activityLogService->log('created', $account, null, null, $account->toArray());

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(UpdateAccountRequest $request, int $id): RedirectResponse
    {
        $account = $this->accountService->update($id, $request->validated());

        $this->activityLogService->log('updated', $account);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $account = \App\Models\Account::findOrFail($id);
        $this->activityLogService->log('deleted', $account, null, $account->toArray(), null);

        $this->accountService->delete($id);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dihapus.');
    }
}
