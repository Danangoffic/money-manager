<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('Categories/Index', [
            'categories' => $this->categoryService->getByHouseholdGrouped($householdId),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create(array_merge(
            $request->validated(),
            ['household_id' => $request->user()->household_id]
        ));

        return redirect()->route('categories.index');
    }

    public function update(StoreCategoryRequest $request, int $id): RedirectResponse
    {
        $this->categoryService->update($id, $request->validated());

        return redirect()->route('categories.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        if (! $this->categoryService->delete($id)) {
            return back()->withErrors(['category' => 'Kategori tidak bisa dihapus karena masih memiliki transaksi.']);
        }

        return redirect()->route('categories.index');
    }
}
