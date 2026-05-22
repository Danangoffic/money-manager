<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(private BudgetService $budgetService) {}

    public function index(Request $request): JsonResponse
    {
        $householdId = $request->user()->household_id;
        $month = $request->get('month', now()->format('Y-m'));

        $budgets = $this->budgetService->getMonthlyOverview($householdId, $month);

        return response()->json([
            'data' => $budgets,
            'month' => $month,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $this->budgetService->createOrUpdate(array_merge($validated, [
            'household_id' => $request->user()->household_id,
        ]));

        return response()->json(['message' => 'Budget berhasil disimpan.'], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        Budget::where('household_id', $request->user()->household_id)->findOrFail($id);

        $this->budgetService->delete($id);

        return response()->json(['message' => 'Budget berhasil dihapus.']);
    }
}
