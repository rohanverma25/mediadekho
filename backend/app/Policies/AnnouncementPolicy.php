<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Announcements are public content shown on the customer dashboard —
     * anyone (including guests, checked at the route/controller level) can
     * browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Announcement $announcement): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('announcement.create');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('announcement.edit');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->can('announcement.delete');
    }
}
