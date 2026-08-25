<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndustryResource;
use App\Models\Industry;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndustryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $industries = Industry::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return IndustryResource::collection($industries);
    }
}
