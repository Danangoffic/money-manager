<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GoalController extends Controller
{
    public function __construct(private GoalService $goalService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $goals = $this->goalService->getByHousehold($request->user()->household_id);

        return GoalResource::collection($goals);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'current_amount' => ['nullable', 'integer', 'min:0'],
            'deadline' => ['nullable', 'date'],
        ]);

        $goal = $this->goalService->create(array_merge($validated, [
            'household_id' => $request->user()->household_id,
        ]));

        return (new GoalResource($goal))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): GoalResource
    {
        $goal = Goal::where('household_id', $request->user()->household_id)
            ->findOrFail($id);

        return new GoalResource($goal);
    }

    public function update(Request $request, int $id): GoalResource
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'current_amount' => ['nullable', 'integer', 'min:0'],
            'deadline' => ['nullable', 'date'],
        ]);

        $goal = $this->goalService->update($id, $validated);

        return new GoalResource($goal);
    }

    public function updateProgress(Request $request, int $id): GoalResource
    {
        $request->validate(['current_amount' => ['required', 'integer', 'min:0']]);

        Goal::where('household_id', $request->user()->household_id)->findOrFail($id);

        $goal = $this->goalService->updateProgress($id, $request->current_amount);

        return new GoalResource($goal);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        Goal::where('household_id', $request->user()->household_id)->findOrFail($id);

        $this->goalService->delete($id);

        return response()->json(['message' => 'Goal berhasil dihapus.']);
    }
}
