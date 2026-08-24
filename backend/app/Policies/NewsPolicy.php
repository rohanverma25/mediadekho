<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    /**
     * News mentions are public marketplace content — anyone (including
     * guests, checked at the route/controller level) can browse them.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, News $news): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('news.create');
    }

    public function update(User $user, News $news): bool
    {
        return $user->can('news.edit');
    }

    public function delete(User $user, News $news): bool
    {
        return $user->can('news.delete');
    }
}
