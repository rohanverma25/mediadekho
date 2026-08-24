<?php

namespace App\Policies;

use App\Models\MediaCategory;
use App\Models\User;

class MediaCategoryPolicy
{
    /**
     * Categories are public marketplace data — anyone (including guests,
     * checked at the route/controller level) can browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, MediaCategory $mediaCategory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('category.create');
    }

    public function update(User $user, MediaCategory $mediaCategory): bool
    {
        return $user->can('category.edit');
    }

    public function delete(User $user, MediaCategory $mediaCategory): bool
    {
        return $user->can('category.delete');
    }

    public function restore(User $user, MediaCategory $mediaCategory): bool
    {
        return $user->can('category.delete');
    }

    public function forceDelete(User $user, MediaCategory $mediaCategory): bool
    {
        return $user->can('category.delete');
    }

    public function import(User $user): bool
    {
        return $user->can('category.import');
    }

    public function export(User $user): bool
    {
        return $user->can('category.export');
    }
}
