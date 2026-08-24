<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MediaInventory extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'media_inventory';

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'frequency_id',
        'language_id',
        'title',
        'short_description',
        'slug',
        'description',
        'image',
        'status',
        'created_by',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image ? Storage::disk('public')->url($this->image) : null,
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MediaCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(MediaCategory::class, 'subcategory_id');
    }

    public function frequency(): BelongsTo
    {
        return $this->belongsTo(Frequency::class, 'frequency_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function price(): HasOne
    {
        return $this->hasOne(MediaInventoryPrice::class, 'inventory_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MediaInventoryImage::class, 'inventory_id')->orderBy('sort_order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MediaInventoryFile::class, 'inventory_id');
    }

    public function keyInsights(): HasMany
    {
        return $this->hasMany(MediaInventoryKeyInsight::class, 'inventory_id')->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'inventory_id')
            ->where('status', 'active')
            ->orderBy('sort_order');
    }

    /**
     * Named distinctly from the `availability` string column (e.g.
     * "available"/"blocked") to avoid Eloquent's relationship resolution
     * shadowing attribute access on $model->availability.
     */
    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(MediaInventoryAvailability::class, 'inventory_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
