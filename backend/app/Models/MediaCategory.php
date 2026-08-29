<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'youtube_video_link',
        'image',
        'icon',
        'status',
        'show_on_homepage',
        'show_on_popular',
        'meta_title',
        'meta_description',
        'meta_image',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'show_on_homepage' => 'boolean',
            'show_on_popular' => 'boolean',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image ? Storage::disk('public')->url($this->image) : null,
        );
    }

    protected function metaImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->meta_image ? Storage::disk('public')->url($this->meta_image) : null,
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(MediaInventory::class, 'category_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id')
            ->where('status', 'active')
            ->orderBy('sort_order');
    }
}
