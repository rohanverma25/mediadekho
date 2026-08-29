<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageMetaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'page_key' => $this->page_key,
            'title' => $this->title,
            'description' => $this->description,
            'og_image_url' => $this->og_image_url,
        ];
    }
}
