<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FaqController extends Controller
{
    /**
     * General-purpose FAQs (not linked to a specific category or inventory
     * item) for the homepage — category/listing pages get their own linked
     * FAQ set from their own detail endpoints.
     */
    public function index(): AnonymousResourceCollection
    {
        $faqs = Faq::query()
            ->where('status', 'active')
            ->whereNull('category_id')
            ->whereNull('inventory_id')
            ->orderBy('sort_order')
            ->get();

        return FaqResource::collection($faqs);
    }
}
