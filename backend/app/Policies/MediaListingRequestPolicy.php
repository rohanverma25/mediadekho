<?php

namespace App\Policies;

use App\Models\MediaListingRequest;
use App\Models\User;

class MediaListingRequestPolicy
{
    /**
     * Like Contact Leads and Job Applications, these are privately
     * submitted vendor details — only staff with media-listing-request.view
     * can browse them in the admin panel.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('media-listing-request.view');
    }

    public function view(User $user, MediaListingRequest $mediaListingRequest): bool
    {
        return $user->can('media-listing-request.view');
    }

    public function update(User $user, MediaListingRequest $mediaListingRequest): bool
    {
        return $user->can('media-listing-request.view');
    }

    public function delete(User $user, MediaListingRequest $mediaListingRequest): bool
    {
        return $user->can('media-listing-request.delete');
    }
}
