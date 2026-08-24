<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\MediaCategory;
use App\Repositories\Contracts\MediaCategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaCategoryController extends Controller
{
    private const IMAGE_DIRECTORY = 'media-categories';

    public function __construct(private readonly MediaCategoryRepositoryInterface $repository)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', MediaCategory::class);

        return view('admin.categories.index');
    }

    /**
     * Flat listing as JSON for the client-side DataTable — the category
     * catalog is small (dozens of rows), so unlike Media Inventory this
     * doesn't need server-side processing.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', MediaCategory::class);

        $categories = MediaCategory::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (MediaCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'status' => $category->status,
                'sort_order' => $category->sort_order,
                'image_url' => $category->image_url,
                'youtube_video_link' => $category->youtube_video_link,
            ]);

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', MediaCategory::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'youtube_video_link' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);
        }

        $category = $this->repository->create($data);

        return response()->json(['message' => 'Category created.', 'category' => $category], 201);
    }

    public function edit(MediaCategory $category): JsonResponse
    {
        $this->authorize('view', $category);

        return response()->json([
            'category' => $category->toArray() + ['image_url' => $category->image_url],
        ]);
    }

    public function update(Request $request, MediaCategory $category): JsonResponse
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'youtube_video_link' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            ImageUploadHelper::delete($category->image);
            $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);
        }

        $category = $this->repository->update($category, $data);

        return response()->json(['message' => 'Category updated.', 'category' => $category]);
    }

    public function destroy(MediaCategory $category): JsonResponse
    {
        $this->authorize('delete', $category);

        // parent_id isn't editable from this form anymore, but existing
        // parent/child relationships (from before this change, or from
        // Media Inventory's sub-category assignment) still live in the
        // database — deleting a category that's still someone's parent
        // would leave those rows pointing at nothing.
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'This category still has sub-categories. Delete or reassign them first.',
            ], 422);
        }

        ImageUploadHelper::delete($category->image);
        $this->repository->delete($category);

        return response()->json(['message' => 'Category deleted.']);
    }
}
