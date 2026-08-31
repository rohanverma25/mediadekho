<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class StaffUserControllerTest extends TestCase
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
        $this->get(route('admin.staff-users.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_role_cannot_access_staff_users_without_the_permission(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.staff-users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_a_staff_user(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)
            ->postJson(route('admin.staff-users.store'), [
                'name' => 'Priya Sharma',
                'email' => 'priya@mediadekho.com',
                'password' => 'password123',
                'role' => 'Admin',
            ])
            ->assertCreated();

        $staff = User::query()->where('email', 'priya@mediadekho.com')->firstOrFail();
        $this->assertTrue($staff->hasRole('Admin'));
        $this->assertSame('approved', $staff->approval_status);
    }

    public function test_a_newly_created_staff_user_can_log_into_the_admin_panel(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $this->actingAs($superAdmin)->postJson(route('admin.staff-users.store'), [
            'name' => 'Priya Sharma',
            'email' => 'priya@mediadekho.com',
            'password' => 'password123',
            'role' => 'Admin',
        ]);

        $this->post(route('admin.login.attempt'), [
            'email' => 'priya@mediadekho.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_customer_tier_role_cannot_be_assigned_via_this_form(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)
            ->postJson(route('admin.staff-users.store'), [
                'name' => 'Should Not Work',
                'email' => 'nope@example.com',
                'password' => 'password123',
                'role' => 'B2B Customer',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');
    }

    public function test_super_admin_cannot_delete_their_own_account(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)
            ->deleteJson(route('admin.staff-users.destroy', $superAdmin))
            ->assertUnprocessable();

        $this->assertNotNull($superAdmin->fresh());
    }

    public function test_super_admin_can_delete_another_staff_user(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $other = $this->userWithRole('Admin');

        $this->actingAs($superAdmin)
            ->deleteJson(route('admin.staff-users.destroy', $other))
            ->assertOk();

        $this->assertNull($other->fresh());
    }

    public function test_customer_accounts_are_not_reachable_through_this_controller(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($superAdmin)
            ->getJson(route('admin.staff-users.edit', $customer))
            ->assertNotFound();
    }

    public function test_staff_data_endpoint_excludes_customers(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $customer = $this->userWithRole('B2B Customer');

        $response = $this->actingAs($superAdmin)->getJson(route('admin.staff-users.data'));

        $emails = collect($response->json('data'))->pluck('email');
        $this->assertTrue($emails->contains($superAdmin->email));
        $this->assertFalse($emails->contains($customer->email));
    }
}
