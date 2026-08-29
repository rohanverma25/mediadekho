<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const PERMISSIONS = [
        'inventory.view',
        'inventory.create',
        'inventory.edit',
        'inventory.delete',
        'inventory.price.manage',
        'inventory.import',
        'inventory.export',
        'category.view',
        'category.create',
        'category.edit',
        'category.delete',
        'category.import',
        'category.export',
        'faq.create',
        'faq.edit',
        'faq.delete',
        'blog.create',
        'blog.edit',
        'blog.delete',
        'news.create',
        'news.edit',
        'news.delete',
        'lead.view',
        'lead.delete',
        'award.create',
        'award.edit',
        'award.delete',
        'award-nomination.view',
        'award-nomination.delete',
        'job.create',
        'job.edit',
        'job.delete',
        'job-application.view',
        'job-application.delete',
        'client-logo.create',
        'client-logo.edit',
        'client-logo.delete',
        'industry.create',
        'industry.edit',
        'industry.delete',
        'stat.create',
        'stat.edit',
        'stat.delete',
        'video.create',
        'video.edit',
        'video.delete',
        'announcement.create',
        'announcement.edit',
        'announcement.delete',
        'settings.edit',
        'page-meta.edit',
        'order.view',
        'order.manage',
    ];

    private const ROLES = [
        'Super Admin',
        'Admin',
        'Retail Customer',
        'B2C Customer',
        'B2B Customer',
        'Enterprise Customer',
    ];

    /**
     * Seed roles and permissions, and grant Super Admin to the existing admin user.
     */
    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Spatie caches the full permission list for performance, and that
        // cache can go stale mid-request the very first time this seeder
        // runs after new permissions were just added above — syncPermissions()
        // below would then fail with "no permission named ..." even though
        // the row was just created a few lines up. Forcing a fresh read
        // here (rather than trusting the model events to have already
        // invalidated it) makes this seeder safe to run at any point in the
        // project's life, not just on a brand-new database.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::findByName('Super Admin');
        $superAdmin->syncPermissions(self::PERMISSIONS);

        $admin = Role::findByName('Admin');
        $admin->syncPermissions(array_diff(self::PERMISSIONS, ['inventory.price.manage']));

        // Customer roles (Retail/B2C/B2B/Enterprise) intentionally have no admin
        // permissions — their role only drives which price tier PricingService
        // resolves for them.

        $existingAdmin = User::query()->where('email', 'dheer725@gmail.com')->first();

        $existingAdmin?->syncRoles(['Super Admin']);
    }
}
