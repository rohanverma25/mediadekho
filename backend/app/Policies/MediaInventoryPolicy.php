<?php

namespace App\Policies;

use App\Models\MediaInventory;
use App\Models\User;

class MediaInventoryPolicy
{
    /**
     * Anyone can browse the listing — the query itself is responsible for
     * scoping to published records for non-staff viewers.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Published inventory is public marketplace data. Draft/archived
     * records are only visible to staff with inventory.view.
     */
    public function view(?User $user, MediaInventory $mediaInventory): bool
    {
        if ($mediaInventory->status === 'published') {
            return true;
        }

        return $user?->can('inventory.view') ?? false;
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.create');
    }

    public function update(User $user, MediaInventory $mediaInventory): bool
    {
        return $user->can('inventory.edit');
    }

    public function delete(User $user, MediaInventory $mediaInventory): bool
    {
        return $user->can('inventory.delete');
    }

    public function restore(User $user, MediaInventory $mediaInventory): bool
    {
        return $user->can('inventory.delete');
    }

    public function forceDelete(User $user, MediaInventory $mediaInventory): bool
    {
        return $user->can('inventory.delete');
    }

    /**
     * Gates access to the full pricing breakdown (all tiers, commission,
     * net profit) — distinct from being able to view an inventory listing.
     */
    public function managePrice(User $user, MediaInventory $mediaInventory): bool
    {
        return $user->can('inventory.price.manage');
    }

    public function import(User $user): bool
    {
        return $user->can('inventory.import');
    }

    public function export(User $user): bool
    {
        return $user->can('inventory.export');
    }
}
