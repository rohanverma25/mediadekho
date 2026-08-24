<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'youtube_video_link',
        'image',
        'main_image',
        'slug',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image ? Storage::disk('public')->url($this->image) : null,
        );
    }

    protected function mainImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->main_image ? Storage::disk('public')->url($this->main_image) : null,
        );
    }
}
