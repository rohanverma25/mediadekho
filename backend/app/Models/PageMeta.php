<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PageMeta extends Model
{
    protected $fillable = [
        'page_key',
        'label',
        'title',
        'description',
        'og_image',
    ];

    protected function ogImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->og_image ? Storage::disk('public')->url($this->og_image) : null,
        );
    }
}
