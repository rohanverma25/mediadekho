<?php

namespace App\Http\Resources;

use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaInventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Pricing is deliberately resolved through PricingService::priceForUser()
     * rather than exposing the MediaInventoryPrice model — this is the API's
     * enforcement point for "never expose other tiers, even if the caller
     * tampers with the request." The resolved tier always comes from
     * $request->user('sanctum') (the Bearer token holder, resolved on demand
     * regardless of whether `auth:sanctum` middleware ran — these routes are
     * intentionally guest-accessible, so no middleware forces auth; a
     * missing/invalid token just resolves to null, i.e. Retail), never from
     * anything the client can pass in.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'status' => $this->status,
            'show_on_deals' => $this->show_on_deals,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_image_url' => $this->meta_image_url,
            'image_url' => $this->image_url,
            'category' => new MediaCategoryResource($this->whenLoaded('category')),
            'subcategory' => $this->whenLoaded('subcategory', fn () => $this->subcategory ? new MediaCategoryResource($this->subcategory) : null),
            'frequency' => $this->whenLoaded('frequency', fn () => $this->frequency?->only(['id', 'name'])),
            'language' => $this->whenLoaded('language', fn () => $this->language?->only(['id', 'name'])),
            'key_insights' => $this->whenLoaded('keyInsights', fn () => $this->keyInsights->map->only(['label', 'value', 'show_after_heading'])),
            'faqs' => $this->whenLoaded('faqs', fn () => FaqResource::collection($this->faqs)),
            'cover_image_url' => $this->whenLoaded('images', fn () => $this->images->firstWhere('is_cover', true)?->url ?? $this->images->first()?->url),
            'images' => $this->whenLoaded('images', fn () => $this->images->pluck('url')),
            'price' => app(PricingService::class)->priceForUser($this->resource, $request->user('sanctum')),
            'created_at' => $this->created_at,
        ];
    }
}
