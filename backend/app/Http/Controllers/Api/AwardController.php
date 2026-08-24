<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AwardResource;
use App\Models\Award;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AwardController extends Controller
{
    /**
     * All active awards (both upcoming and past) — the frontend groups them
     * by `type` into the "Upcoming" and "Past Association Awards" sections.
     */
    public function index(): AnonymousResourceCollection
    {
        $awards = Award::query()
            ->where('status', 'active')
            ->orderBy('event_date', 'desc')
            ->orderBy('sort_order')
            ->get();

        return AwardResource::collection($awards);
    }
}
