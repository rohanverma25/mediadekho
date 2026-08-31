<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientLogoResource extends JsonResource
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
            'logo_url' => $this->logo_url,
            'website_url' => $this->website_url,
            'industry' => $this->whenLoaded('industry', fn () => $this->industry ? [
                'id' => $this->industry->id,
                'title' => $this->industry->title,
            ] : null),
        ];
    }
}
