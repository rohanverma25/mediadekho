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
            'show_on_homepage' => $this->show_on_homepage,
            'show_on_popular' => $this->show_on_popular,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_image_url' => $this->meta_image_url,
            'inventory_count' => $this->whenCounted('inventories'),
            'children' => self::collection($this->whenLoaded('children')),
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
        ];
    }
}
