<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    private const IMAGE_DIRECTORY = 'blogs';

    public function index(): View
    {
        $this->authorize('viewAny', Blog::class);

        return view('admin.blogs.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Blog::class);

        $blogs = Blog::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Blog $blog) => [
                'id' => $blog->id,
                'title' => $blog->title,
                'excerpt_preview' => Str::limit(strip_tags($blog->excerpt ?? ''), 80),
                'author_name' => $blog->author_name,
                'status' => $blog->status,
                'published_at' => $blog->published_at?->format('Y-m-d'),
                'featured_image_url' => $blog->featured_image_url,
            ]);

        return response()->json(['data' => $blogs]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Blog::class);

        $data = $this->validated($request);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = ImageUploadHelper::upload($request->file('featured_image'), self::IMAGE_DIRECTORY);
        }

        $blog = Blog::query()->create($data);

        return response()->json(['message' => 'Blog post created.', 'blog' => $blog], 201);
    }

    public function edit(Blog $blog): JsonResponse
    {
        $this->authorize('view', $blog);

        return response()->json([
            'blog' => $blog->toArray() + ['featured_image_url' => $blog->featured_image_url],
        ]);
    }

    public function update(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('update', $blog);

        $data = $this->validated($request);

        if ($request->hasFile('featured_image')) {
            ImageUploadHelper::delete($blog->featured_image);
            $data['featured_image'] = ImageUploadHelper::upload($request->file('featured_image'), self::IMAGE_DIRECTORY);
        }

        $blog->update($data);

        return response()->json(['message' => 'Blog post updated.', 'blog' => $blog]);
    }

    public function destroy(Blog $blog): JsonResponse
    {
        $this->authorize('delete', $blog);

        ImageUploadHelper::delete($blog->featured_image);
        $blog->delete();

        return response()->json(['message' => 'Blog post deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'string', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
