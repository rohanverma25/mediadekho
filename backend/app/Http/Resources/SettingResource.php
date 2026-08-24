<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'logo_url' => $this->logo_url,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'contact_address' => $this->contact_address,
            'footer_description' => $this->footer_description,
            'social' => [
                'facebook' => $this->facebook_url,
                'twitter' => $this->twitter_url,
                'linkedin' => $this->linkedin_url,
                'youtube' => $this->youtube_url,
            ],
            'header_scripts' => $this->header_scripts,
            'footer_scripts' => $this->footer_scripts,
            'privacy_policy' => $this->privacy_policy,
            'terms_of_use' => $this->terms_of_use,
            'contact_emails' => $this->contact_emails ?? [],
            'contact_addresses' => $this->contact_addresses ?? [],
            'map_embed_url' => $this->map_embed_url,
            // Public by design — Razorpay's Checkout widget needs this key
            // client-side to open the payment modal. razorpay_key_secret
            // must NEVER be added here; it stays server-side only.
            'razorpay_key_id' => $this->razorpay_key_id,
        ];
    }
}
