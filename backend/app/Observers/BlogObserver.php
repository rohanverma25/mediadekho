<?php

namespace App\Observers;

use App\Helpers\SlugHelper;
use App\Models\Blog;

class BlogObserver
{
    public function creating(Blog $blog): void
    {
        if (blank($blog->slug)) {
            $blog->slug = SlugHelper::unique(Blog::class, $blog->title, 'slug');
        }
    }

    public function updating(Blog $blog): void
    {
        if ($blog->isDirty('title') && ! $blog->isDirty('slug')) {
            $blog->slug = SlugHelper::unique(Blog::class, $blog->title, 'slug', $blog->id);
        }
    }
}
