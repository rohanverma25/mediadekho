<?php

namespace App\Policies;

use App\Models\Magazine;
use App\Models\User;

class MagazinePolicy
{
    /**
     * Magazines are public marketplace content (the Magazine Reader page) —
     * anyone, including guests, can browse and read them; the query itself
     * scopes to `status = active` for non-staff viewers.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Magazine $magazine): bool
    {
        if ($magazine->status === 'active') {
            return true;
        }

        return $user?->can('magazine.edit') ?? false;
    }

    public function create(User $user): bool
    {
        return $user->can('magazine.create');
    }

    public function update(User $user, Magazine $magazine): bool
    {
        return $user->can('magazine.edit');
    }

    public function delete(User $user, Magazine $magazine): bool
    {
        return $user->can('magazine.delete');
    }
}
