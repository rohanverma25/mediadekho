<?php

namespace App\Observers;

use App\Helpers\SlugHelper;
use App\Models\MediaCategory;

class MediaCategoryObserver
{
    public function creating(MediaCategory $category): void
    {
        if (blank($category->slug)) {
            $category->slug = SlugHelper::unique(MediaCategory::class, $category->name);
        }
    }

    public function updating(MediaCategory $category): void
    {
        if ($category->isDirty('name') && ! $category->isDirty('slug')) {
            $category->slug = SlugHelper::unique(MediaCategory::class, $category->name, 'slug', $category->id);
        }
    }
}
