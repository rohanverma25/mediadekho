<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaInventoryFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->path ? Storage::disk('public')->url($this->path) : null,
        );
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(MediaInventory::class, 'inventory_id');
    }
}
