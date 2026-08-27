<?php

namespace App\Policies;

use App\Models\Stat;
use App\Models\User;

class StatPolicy
{
    /**
     * Stats are public marketplace content (homepage counters) — anyone
     * (including guests, checked at the route/controller level) can browse
     * them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Stat $stat): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('stat.create');
    }

    public function update(User $user, Stat $stat): bool
    {
        return $user->can('stat.edit');
    }

    public function delete(User $user, Stat $stat): bool
    {
        return $user->can('stat.delete');
    }
}
