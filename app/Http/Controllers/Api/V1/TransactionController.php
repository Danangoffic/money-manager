<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $householdId = $request->user()->household_id;

        $transactions = $this->transactionService->getByHouseholdFiltered($householdId, $request->all());

        return TransactionResource::collection($transactions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'type' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'transfer_to_account_id' => ['nullable', 'exists:accounts,id', 'different:account_id'],
        ]);

        $transaction = $this->transactionService->create(array_merge($validated, [
            'household_id' => $request->user()->household_id,
            'user_id' => $request->user()->id,
        ]));

        return (new TransactionResource($transaction->load(['account', 'category', 'user'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): TransactionResource
    {
        $transaction = \App\Models\Transaction::with(['account', 'category', 'user', 'transferToAccount'])
            ->where('household_id', $request->user()->household_id)
            ->findOrFail($id);

        return new TransactionResource($transaction);
    }

    public function update(Request $request, int $id): TransactionResource
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'type' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'transfer_to_account_id' => ['nullable', 'exists:accounts,id', 'different:account_id'],
        ]);

        $transaction = $this->transactionService->update($id, array_merge($validated, [
            'household_id' => $request->user()->household_id,
            'user_id' => $request->user()->id,
        ]));

        return new TransactionResource($transaction->load(['account', 'category', 'user']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        // Verify ownership
        \App\Models\Transaction::where('household_id', $request->user()->household_id)->findOrFail($id);

        $this->transactionService->delete($id);

        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }
}
