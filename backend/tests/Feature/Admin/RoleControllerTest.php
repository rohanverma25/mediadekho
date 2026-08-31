<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.roles.index'))
            ->assertRedirect(route('admin.login'));
    }

    /**
     * role.manage is Super-Admin-only — a plain Admin account (which has
     * every other permission) must still be refused here.
     */
    public function test_admin_role_cannot_access_roles_without_role_manage_permission(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_roles_index(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)
            ->get(route('admin.roles.index'))
            ->assertOk();
    }

    /**
     * The four customer-tier roles are a separate concern (they only drive
     * pricing) and must never appear in this admin-panel-only screen.
     */
    public function test_customer_tier_roles_are_excluded_from_the_list(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($superAdmin)->getJson(route('admin.roles.data'));

        $names = collect($response->json('data'))->pluck('name');
        $this->assertFalse($names->contains('B2B Customer'));
        $this->assertTrue($names->contains('Super Admin'));
        $this->assertTrue($names->contains('Admin'));
    }

    public function test_super_admin_can_create_a_custom_role_with_permissions(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)
            ->post(route('admin.roles.store'), [
                'name' => 'Content Editor',
                'permissions' => ['blog.create', 'blog.edit'],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('Content Editor');
        $this->assertEqualsCanonicalizing(['blog.create', 'blog.edit'], $role->permissions->pluck('name')->all());
    }

    /**
     * The whole point of a permission-driven `staff` middleware (instead of
     * hardcoding "Super Admin"/"Admin") is that a brand new custom role
     * works immediately — this is the behavior that actually proves it.
     */
    public function test_a_user_with_a_custom_role_can_access_the_admin_panel(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $this->actingAs($superAdmin)->post(route('admin.roles.store'), [
            'name' => 'Content Editor',
            'permissions' => ['blog.create', 'blog.edit'],
        ]);

        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor)
            ->get(route('admin.dashboard'))
            ->assertOk();

        // ...but still can't reach a module they weren't granted.
        $this->actingAs($editor)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_creating_a_role_named_after_a_customer_tier_is_rejected(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)
            ->postJson(route('admin.roles.store'), [
                'name' => 'B2B Customer',
                'permissions' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_a_protected_roles_name_cannot_be_changed_but_its_permissions_can(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $adminRole = Role::findByName('Admin');

        $this->actingAs($superAdmin)
            ->put(route('admin.roles.update', $adminRole), [
                'name' => 'Renamed Admin',
                'permissions' => ['blog.create'],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $adminRole->refresh();
        $this->assertSame('Admin', $adminRole->name);
        $this->assertEqualsCanonicalizing(['blog.create'], $adminRole->permissions->pluck('name')->all());
    }

    public function test_a_protected_role_cannot_be_deleted(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $adminRole = Role::findByName('Admin');

        $this->actingAs($superAdmin)
            ->deleteJson(route('admin.roles.destroy', $adminRole))
            ->assertUnprocessable();

        $this->assertNotNull(Role::findByName('Admin'));
    }

    public function test_a_role_still_assigned_to_a_user_cannot_be_deleted(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $this->actingAs($superAdmin)->post(route('admin.roles.store'), ['name' => 'Content Editor', 'permissions' => []]);
        $this->userWithRole('Content Editor');

        $role = Role::findByName('Content Editor');

        $this->actingAs($superAdmin)
            ->deleteJson(route('admin.roles.destroy', $role))
            ->assertUnprocessable();

        $this->assertNotNull(Role::findByName('Content Editor'));
    }

    public function test_an_unused_custom_role_can_be_deleted(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $this->actingAs($superAdmin)->post(route('admin.roles.store'), ['name' => 'Content Editor', 'permissions' => []]);
        $role = Role::findByName('Content Editor');

        $this->actingAs($superAdmin)
            ->deleteJson(route('admin.roles.destroy', $role))
            ->assertOk();

        $this->assertNull(Role::find($role->id));
    }

    public function test_customer_tier_role_is_not_reachable_by_id(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $customerRole = Role::findByName('B2B Customer');

        $this->actingAs($superAdmin)
            ->get(route('admin.roles.edit', $customerRole))
            ->assertNotFound();
    }
}
