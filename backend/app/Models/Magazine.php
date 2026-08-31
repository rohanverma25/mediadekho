<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Magazine extends Model
{
    /** @use HasFactory<\Database\Factories\MagazineFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'pdf_file',
        'published_at',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'sort_order' => 'integer',
        ];
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null,
        );
    }

    protected function pdfUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->pdf_file ? Storage::disk('public')->url($this->pdf_file) : null,
        );
    }
}
