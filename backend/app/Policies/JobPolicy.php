<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    /**
     * Job listings are public content — anyone (including guests, checked
     * at the route/controller level) can browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Job $job): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('job.create');
    }

    public function update(User $user, Job $job): bool
    {
        return $user->can('job.edit');
    }

    public function delete(User $user, Job $job): bool
    {
        return $user->can('job.delete');
    }
}
