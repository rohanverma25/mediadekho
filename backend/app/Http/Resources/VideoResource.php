<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * The frontend never gets the raw youtube_url — just the bare video ID
     * and a static thumbnail, so it can render a lightweight thumbnail
     * facade and only load YouTube's actual (heavy) iframe player once a
     * viewer clicks play.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'source_type' => $this->source_type,
            'video_id' => $this->video_id,
            'video_url' => $this->video_url,
            'thumbnail_url' => $this->thumbnail_url,
        ];
    }
}
