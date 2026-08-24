<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientLogoResource;
use App\Models\ClientLogo;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientLogoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $logos = ClientLogo::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return ClientLogoResource::collection($logos);
    }
}
