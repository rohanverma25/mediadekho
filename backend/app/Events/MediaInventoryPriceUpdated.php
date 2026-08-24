<?php

namespace App\Events;

use App\Models\MediaInventoryPrice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaInventoryPriceUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MediaInventoryPrice $price)
    {
    }
}
