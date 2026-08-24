<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    /**
     * Settings drive public site chrome (logo, contact info, footer,
     * tracking scripts) — anyone can read them, but only privileged staff
     * can change them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Setting $setting): bool
    {
        return true;
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->can('settings.edit');
    }
}
