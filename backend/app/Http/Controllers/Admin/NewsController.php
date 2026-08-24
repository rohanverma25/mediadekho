<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const IMAGE_DIRECTORY = 'news';

    public function index(): View
    {
        $this->authorize('viewAny', News::class);

        return view('admin.news.index');
    }

    /**
     * Flat listing as JSON for the client-side DataTable — the news catalog
     * is small, so unlike Media Inventory this doesn't need server-side processing.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', News::class);

        $news = News::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (News $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'link' => $item->link,
                'status' => $item->status,
                'sort_order' => $item->sort_order,
                'image_url' => $item->image_url,
            ]);

        return response()->json(['data' => $news]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', News::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link' => ['required', 'url', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);

        $news = News::query()->create($data);

        return response()->json(['message' => 'News item created.', 'news' => $news], 201);
    }

    public function edit(News $news): JsonResponse
    {
        $this->authorize('view', $news);

        return response()->json([
            'news' => $news->toArray() + ['image_url' => $news->image_url],
        ]);
    }

    public function update(Request $request, News $news): JsonResponse
    {
        $this->authorize('update', $news);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link' => ['required', 'url', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            ImageUploadHelper::delete($news->image);
            $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);
        }

        $news->update($data);

        return response()->json(['message' => 'News item updated.', 'news' => $news]);
    }

    public function destroy(News $news): JsonResponse
    {
        $this->authorize('delete', $news);

        ImageUploadHelper::delete($news->image);
        $news->delete();

        return response()->json(['message' => 'News item deleted.']);
    }
}
