<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MagazineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image_url' => $this->cover_image_url,
            'pdf_url' => $this->pdf_url,
            // Same file, served through an /api/* route (CORS-friendly for
            // the in-browser reader's JS fetch) rather than the static
            // /uploads/... URL above, which is used for the plain
            // download/"open in new tab" link instead.
            'pdf_stream_url' => url("/api/magazines/{$this->slug}/pdf"),
            'published_at' => $this->published_at?->format('Y-m-d'),
        ];
    }
}
