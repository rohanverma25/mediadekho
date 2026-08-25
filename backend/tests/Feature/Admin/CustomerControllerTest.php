<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class CustomerControllerTest extends TestCase
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
        $this->get(route('admin.customers.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_customers_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.customers.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_customers_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk();
    }

    public function test_data_endpoint_only_lists_customer_roles_with_order_counts(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $b2bCustomer = $this->userWithRole('B2B Customer');
        $retailCustomer = $this->userWithRole('Retail Customer');

        Order::factory()->count(2)->create(['user_id' => $b2bCustomer->id]);

        $response = $this->actingAs($admin)->getJson(route('admin.customers.data'));

        $response->assertOk()->assertJsonCount(2, 'data');

        $rows = collect($response->json('data'));
        $this->assertTrue($rows->contains('id', $b2bCustomer->id));
        $this->assertTrue($rows->contains('id', $retailCustomer->id));
        $this->assertFalse($rows->contains('id', $admin->id));
        $this->assertSame(2, $rows->firstWhere('id', $b2bCustomer->id)['orders_count']);
    }

    public function test_super_admin_can_approve_a_pending_customer(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $pendingB2B = $this->userWithRole('B2B Customer');
        $pendingB2B->update(['approval_status' => 'pending']);

        $this->actingAs($admin)
            ->putJson(route('admin.customers.approve', $pendingB2B))
            ->assertOk();

        $this->assertSame('approved', $pendingB2B->fresh()->approval_status);
    }

    public function test_super_admin_can_reject_a_pending_customer(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $pendingB2B = $this->userWithRole('B2B Customer');
        $pendingB2B->update(['approval_status' => 'pending']);

        $this->actingAs($admin)
            ->putJson(route('admin.customers.reject', $pendingB2B))
            ->assertOk();

        $this->assertSame('rejected', $pendingB2B->fresh()->approval_status);
    }

    public function test_customer_role_cannot_approve_customers(): void
    {
        $customer = $this->userWithRole('B2B Customer');
        $pendingB2B = User::factory()->create(['approval_status' => 'pending']);

        $this->actingAs($customer)
            ->putJson(route('admin.customers.approve', $pendingB2B))
            ->assertForbidden();
    }
}
