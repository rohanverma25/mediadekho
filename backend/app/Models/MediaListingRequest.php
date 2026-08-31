<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaListingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'media_title',
        'media_type',
        'location',
        'approximate_rate',
        'description',
        'image',
        'media_kit',
        'media_kit_original_name',
        'status',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image ? Storage::disk('public')->url($this->image) : null,
        );
    }

    protected function mediaKitUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->media_kit ? Storage::disk('public')->url($this->media_kit) : null,
        );
    }
}
