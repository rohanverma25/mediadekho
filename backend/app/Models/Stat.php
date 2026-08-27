<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    /** @use HasFactory<\Database\Factories\StatFactory> */
    use HasFactory;

    protected $fillable = [
        'value',
        'label',
        'icon',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Falls back to a generic icon so the homepage tile never renders blank
     * if an admin skips the (optional) icon field.
     */
    protected function displayIcon(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->icon ?: 'fa-solid fa-chart-simple',
        );
    }
}
