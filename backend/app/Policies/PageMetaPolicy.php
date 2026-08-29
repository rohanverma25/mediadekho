<?php

namespace App\Policies;

use App\Models\PageMeta;
use App\Models\User;

class PageMetaPolicy
{
    /**
     * Meta tags drive public page <head> content — anyone can read them,
     * but only privileged staff can change them. There's no create/delete:
     * the set of editable pages is fixed and seeded by migration.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, PageMeta $pageMeta): bool
    {
        return true;
    }

    public function update(User $user, PageMeta $pageMeta): bool
    {
        return $user->can('page-meta.edit');
    }
}
