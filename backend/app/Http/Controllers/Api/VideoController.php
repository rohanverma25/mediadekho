<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $videos = Video::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return VideoResource::collection($videos);
    }
}
