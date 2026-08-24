<?php

namespace App\Policies;

use App\Models\AwardNomination;
use App\Models\User;

class AwardNominationPolicy
{
    /**
     * Like Contact Leads, nominations are private submitted data — only
     * staff with award-nomination.view can browse them in the admin panel.
     * A nominator's own submissions are read through a separate "my
     * nominations" endpoint, not this policy.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('award-nomination.view');
    }

    public function view(User $user, AwardNomination $awardNomination): bool
    {
        return $user->can('award-nomination.view');
    }

    public function update(User $user, AwardNomination $awardNomination): bool
    {
        return $user->can('award-nomination.view');
    }

    public function delete(User $user, AwardNomination $awardNomination): bool
    {
        return $user->can('award-nomination.delete');
    }
}
