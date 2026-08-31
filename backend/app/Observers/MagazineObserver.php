<?php

namespace App\Observers;

use App\Helpers\SlugHelper;
use App\Models\Magazine;

class MagazineObserver
{
    public function creating(Magazine $magazine): void
    {
        if (blank($magazine->slug)) {
            $magazine->slug = SlugHelper::unique(Magazine::class, $magazine->title, 'slug');
        }
    }

    public function updating(Magazine $magazine): void
    {
        if ($magazine->isDirty('title') && ! $magazine->isDirty('slug')) {
            $magazine->slug = SlugHelper::unique(Magazine::class, $magazine->title, 'slug', $magazine->id);
        }
    }
}
