<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientLogoResource;
use App\Models\ClientLogo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientLogoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $logos = ClientLogo::query()
            ->where('status', 'active')
            ->when($request->filled('industry_id'), fn ($q) => $q->where('industry_id', $request->input('industry_id')))
            ->with('industry')
            ->orderBy('sort_order')
            ->get();

        return ClientLogoResource::collection($logos);
    }
}
