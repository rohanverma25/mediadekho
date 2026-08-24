<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class BlogController extends Controller
{
    /**
     * Published posts for the public blog listing, newest first.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $blogs = Blog::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->paginate((int) $request->input('per_page', 9));

        return BlogResource::collection($blogs);
    }

    /**
     * No `auth:sanctum` middleware guards this route (guests must be able to
     * read posts), so authorization explicitly checks the sanctum-resolved
     * user rather than $this->authorize()'s default guard.
     */
    public function show(Request $request, Blog $blog): BlogResource
    {
        Gate::forUser($request->user('sanctum'))->authorize('view', $blog);

        return new BlogResource($blog);
    }
}
