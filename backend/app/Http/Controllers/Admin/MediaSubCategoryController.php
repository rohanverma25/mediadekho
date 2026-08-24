<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaCategory;
use App\Repositories\Contracts\MediaCategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaSubCategoryController extends Controller
{
    public function __construct(private readonly MediaCategoryRepositoryInterface $repository)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', MediaCategory::class);

        $parents = MediaCategory::query()->whereNull('parent_id')->orderBy('name')->get();

        return view('admin.subcategories.index', compact('parents'));
    }

    /**
     * Flat listing of sub-categories only (parent_id set) as JSON for the
     * client-side DataTable.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', MediaCategory::class);

        $subcategories = MediaCategory::query()
            ->whereNotNull('parent_id')
            ->with('parent')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (MediaCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id,
                'parent' => $category->parent?->name,
                'status' => $category->status,
                'sort_order' => $category->sort_order,
            ]);

        return response()->json(['data' => $subcategories]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', MediaCategory::class);

        $data = $request->validate([
            'parent_id' => ['required', 'integer', 'exists:media_categories,id', $this->parentMustBeTopLevelRule()],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $subcategory = $this->repository->create($data);

        return response()->json(['message' => 'Sub-category created.', 'category' => $subcategory], 201);
    }

    public function edit(MediaCategory $subcategory): JsonResponse
    {
        $this->authorize('view', $subcategory);

        return response()->json(['category' => $subcategory]);
    }

    public function update(Request $request, MediaCategory $subcategory): JsonResponse
    {
        $this->authorize('update', $subcategory);

        $data = $request->validate([
            'parent_id' => [
                'required', 'integer', 'exists:media_categories,id',
                $this->parentMustBeTopLevelRule(),
                function ($attribute, $value, $fail) use ($subcategory) {
                    if ((int) $value === $subcategory->id) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $subcategory = $this->repository->update($subcategory, $data);

        return response()->json(['message' => 'Sub-category updated.', 'category' => $subcategory]);
    }

    public function destroy(MediaCategory $subcategory): JsonResponse
    {
        $this->authorize('delete', $subcategory);

        $this->repository->delete($subcategory);

        return response()->json(['message' => 'Sub-category deleted.']);
    }

    /**
     * Keeps the hierarchy at two levels: the chosen parent must itself be
     * top-level, so a sub-category can never become another sub-category's
     * parent.
     */
    private function parentMustBeTopLevelRule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if (! $value) {
                return;
            }

            $parent = MediaCategory::find($value);

            if ($parent && $parent->parent_id !== null) {
                $fail('The selected parent must be a top-level category.');
            }
        };
    }
}
