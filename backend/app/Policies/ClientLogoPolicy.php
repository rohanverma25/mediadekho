<?php

namespace App\Policies;

use App\Models\ClientLogo;
use App\Models\User;

class ClientLogoPolicy
{
    /**
     * Client logos are public marketplace content (homepage marquee) —
     * anyone (including guests, checked at the route/controller level) can
     * browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ClientLogo $clientLogo): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('client-logo.create');
    }

    public function update(User $user, ClientLogo $clientLogo): bool
    {
        return $user->can('client-logo.edit');
    }

    public function delete(User $user, ClientLogo $clientLogo): bool
    {
        return $user->can('client-logo.delete');
    }
}
