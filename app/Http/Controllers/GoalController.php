<?php

namespace App\Http\Controllers;

use App\Http\Requests\Goal\StoreGoalRequest;
use App\Services\GoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    public function __construct(private GoalService $goalService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Goals/Index', [
            'goals' => $this->goalService->getByHousehold($request->user()->household_id),
        ]);
    }

    public function store(StoreGoalRequest $request): RedirectResponse
    {
        $this->goalService->create(array_merge(
            $request->validated(),
            ['household_id' => $request->user()->household_id]
        ));

        return redirect()->route('goals.index')->with('success', 'Goal berhasil ditambahkan.');
    }

    public function update(StoreGoalRequest $request, int $id): RedirectResponse
    {
        $this->goalService->update($id, $request->validated());

        return redirect()->route('goals.index')->with('success', 'Goal berhasil diperbarui.');
    }

    public function updateProgress(Request $request, int $id): RedirectResponse
    {
        $request->validate(['current_amount' => ['required', 'integer', 'min:0']]);

        $this->goalService->updateProgress($id, $request->current_amount);

        return back()->with('success', 'Progress goal berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->goalService->delete($id);

        return redirect()->route('goals.index')->with('success', 'Goal berhasil dihapus.');
    }
}
