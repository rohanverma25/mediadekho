<?php

namespace Tests\Traits;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\PermissionRegistrar;

trait SeedsRolesAndPermissions
{
    protected function seedRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function userWithRole(?string $role = null): User
    {
        $user = User::factory()->create();

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }
}
