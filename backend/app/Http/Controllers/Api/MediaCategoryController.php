<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaCategoryResource;
use App\Models\MediaCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MediaCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = MediaCategory::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->withCount(['inventories' => fn ($q) => $q->where('status', 'published')])
            ->with(['children' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('sort_order')
            ->get();

        return MediaCategoryResource::collection($categories);
    }

    public function show(MediaCategory $category): MediaCategoryResource
    {
        $category->loadCount(['inventories' => fn ($q) => $q->where('status', 'published')]);
        $category->load(['children' => fn ($q) => $q->where('status', 'active'), 'faqs']);

        return new MediaCategoryResource($category);
    }
}
