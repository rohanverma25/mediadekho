<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Video;

class VideoPolicy
{
    /**
     * Videos are public marketplace content (homepage slider) — anyone
     * (including guests, checked at the route/controller level) can browse
     * them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Video $video): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('video.create');
    }

    public function update(User $user, Video $video): bool
    {
        return $user->can('video.edit');
    }

    public function delete(User $user, Video $video): bool
    {
        return $user->can('video.delete');
    }
}
