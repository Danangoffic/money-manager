<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $accounts = $this->accountService->getByHousehold($request->user()->household_id);

        return AccountResource::collection($accounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['cash', 'bank', 'e-wallet', 'credit-card'])],
            'balance' => ['nullable', 'integer'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $account = $this->accountService->create(array_merge($validated, [
            'household_id' => $request->user()->household_id,
        ]));

        return (new AccountResource($account))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): AccountResource
    {
        $account = \App\Models\Account::where('household_id', $request->user()->household_id)
            ->findOrFail($id);

        return new AccountResource($account);
    }

    public function update(Request $request, int $id): AccountResource
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['cash', 'bank', 'e-wallet', 'credit-card'])],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $account = $this->accountService->update($id, $validated);

        return new AccountResource($account);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        \App\Models\Account::where('household_id', $request->user()->household_id)->findOrFail($id);

        $this->accountService->delete($id);

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }
}
