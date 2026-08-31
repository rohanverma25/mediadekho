<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaInventoryKeyInsight extends Model
{
    protected $fillable = [
        'inventory_id',
        'label',
        'value',
        'show_after_heading',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'show_after_heading' => 'boolean',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(MediaInventory::class, 'inventory_id');
    }
}
