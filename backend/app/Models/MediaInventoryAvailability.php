<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaInventoryAvailability extends Model
{
    use HasFactory;

    protected $table = 'media_inventory_availability';

    protected $fillable = [
        'inventory_id',
        'date',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(MediaInventory::class, 'inventory_id');
    }
}
