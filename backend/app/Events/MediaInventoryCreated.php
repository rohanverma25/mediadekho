<?php

namespace App\Events;

use App\Models\MediaInventory;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaInventoryCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MediaInventory $inventory)
    {
    }
}
