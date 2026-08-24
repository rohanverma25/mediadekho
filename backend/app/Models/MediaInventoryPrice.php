<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MediaInventoryPrice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'inventory_id',
        'base_price',
        'retail_percentage',
        'b2c_percentage',
        'b2b_percentage',
        'retail_price',
        'b2c_price',
        'b2b_price',
        'enterprise_price',
        'discount_type',
        'discount_value',
        'tax_percentage',
        'commission_percentage',
        'platform_margin',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'retail_percentage' => 'decimal:2',
            'b2c_percentage' => 'decimal:2',
            'b2b_percentage' => 'decimal:2',
            'retail_price' => 'decimal:2',
            'b2c_price' => 'decimal:2',
            'b2b_price' => 'decimal:2',
            'enterprise_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'platform_margin' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(MediaInventory::class, 'inventory_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['base_price', 'retail_percentage', 'b2c_percentage', 'b2b_percentage', 'retail_price', 'b2c_price', 'b2b_price', 'enterprise_price', 'discount_type', 'discount_value', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
