<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NewsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $news = News::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return NewsResource::collection($news);
    }
}
