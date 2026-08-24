<?php

namespace App\Observers;

use App\Helpers\SlugHelper;
use App\Models\MediaInventory;

class MediaInventoryObserver
{
    public function creating(MediaInventory $inventory): void
    {
        if (blank($inventory->slug)) {
            $inventory->slug = SlugHelper::unique(MediaInventory::class, $inventory->title, 'slug');
        }
    }

    public function updating(MediaInventory $inventory): void
    {
        if ($inventory->isDirty('title') && ! $inventory->isDirty('slug')) {
            $inventory->slug = SlugHelper::unique(MediaInventory::class, $inventory->title, 'slug', $inventory->id);
        }
    }
}
