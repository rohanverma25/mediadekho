<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    /**
     * FAQs are public marketplace content — anyone (including guests,
     * checked at the route/controller level) can browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Faq $faq): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('faq.create');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->can('faq.edit');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->can('faq.delete');
    }
}
