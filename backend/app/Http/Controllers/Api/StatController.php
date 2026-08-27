<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatResource;
use App\Models\Stat;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $stats = Stat::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return StatResource::collection($stats);
    }
}
