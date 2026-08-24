<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;

class BlogPolicy
{
    /**
     * Anyone can browse the listing — the query itself is responsible for
     * scoping to published posts for non-staff viewers.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Published posts are public content. Draft posts are only visible to
     * staff with blog.edit (e.g. previewing before publishing).
     */
    public function view(?User $user, Blog $blog): bool
    {
        if ($blog->status === 'published') {
            return true;
        }

        return $user?->can('blog.edit') ?? false;
    }

    public function create(User $user): bool
    {
        return $user->can('blog.create');
    }

    public function update(User $user, Blog $blog): bool
    {
        return $user->can('blog.edit');
    }

    public function delete(User $user, Blog $blog): bool
    {
        return $user->can('blog.delete');
    }
}
