<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AwardNominationResource extends JsonResource
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
            'award' => [
                'id' => $this->award?->id,
                'title' => $this->award?->title,
                'image_url' => $this->award?->image_url,
            ],
            'subject' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}
