<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'youtube_video_link' => $this->youtube_video_link,
            'image_url' => $this->image_url,
            'icon' => $this->icon,
            'inventory_count' => $this->whenCounted('inventories'),
            'children' => self::collection($this->whenLoaded('children')),
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
        ];
    }
}
