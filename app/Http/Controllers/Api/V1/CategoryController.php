<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryService->getByHousehold($request->user()->household_id);

        return CategoryResource::collection($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $category = $this->categoryService->create(array_merge($validated, [
            'household_id' => $request->user()->household_id,
        ]));

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): CategoryResource
    {
        $category = \App\Models\Category::where('household_id', $request->user()->household_id)
            ->findOrFail($id);

        return new CategoryResource($category);
    }

    public function update(Request $request, int $id): CategoryResource
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $category = $this->categoryService->update($id, $validated);

        return new CategoryResource($category);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        \App\Models\Category::where('household_id', $request->user()->household_id)->findOrFail($id);

        if (! $this->categoryService->delete($id)) {
            return response()->json(['message' => 'Kategori tidak bisa dihapus karena masih memiliki transaksi.'], 422);
        }

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
