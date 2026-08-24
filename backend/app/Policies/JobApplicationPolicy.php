<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    /**
     * Like Contact Leads and Award Nominations, applications are private
     * submitted data — only staff with job-application.view can browse
     * them in the admin panel.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('job-application.view');
    }

    public function view(User $user, JobApplication $jobApplication): bool
    {
        return $user->can('job-application.view');
    }

    public function update(User $user, JobApplication $jobApplication): bool
    {
        return $user->can('job-application.view');
    }

    public function delete(User $user, JobApplication $jobApplication): bool
    {
        return $user->can('job-application.delete');
    }
}
