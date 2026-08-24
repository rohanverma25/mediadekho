<?php

namespace App\Policies;

use App\Models\ContactLead;
use App\Models\User;

class ContactLeadPolicy
{
    /**
     * Unlike the rest of the admin's content modules, leads are private
     * submitted data — nobody outside staff with lead.view should ever be
     * able to browse or read them (there's no public-facing counterpart).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('lead.view');
    }

    public function view(User $user, ContactLead $contactLead): bool
    {
        return $user->can('lead.view');
    }

    public function update(User $user, ContactLead $contactLead): bool
    {
        return $user->can('lead.view');
    }

    public function delete(User $user, ContactLead $contactLead): bool
    {
        return $user->can('lead.delete');
    }
}
