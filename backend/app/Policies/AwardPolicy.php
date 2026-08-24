<?php

namespace App\Policies;

use App\Models\Award;
use App\Models\User;

class AwardPolicy
{
    /**
     * Awards (upcoming events and past association awards) are public
     * marketing content — anyone (including guests, checked at the
     * route/controller level) can browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Award $award): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('award.create');
    }

    public function update(User $user, Award $award): bool
    {
        return $user->can('award.edit');
    }

    public function delete(User $user, Award $award): bool
    {
        return $user->can('award.delete');
    }
}
