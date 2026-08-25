<?php

namespace App\Policies;

use App\Models\Industry;
use App\Models\User;

class IndustryPolicy
{
    /**
     * Industries are public marketplace content (homepage section) —
     * anyone (including guests, checked at the route/controller level) can
     * browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Industry $industry): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('industry.create');
    }

    public function update(User $user, Industry $industry): bool
    {
        return $user->can('industry.edit');
    }

    public function delete(User $user, Industry $industry): bool
    {
        return $user->can('industry.delete');
    }
}
