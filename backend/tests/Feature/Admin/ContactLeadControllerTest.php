<?php

namespace Tests\Feature\Admin;

use App\Models\ContactLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class ContactLeadControllerTest extends TestCase
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
        $this->get(route('admin.leads.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_leads_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.leads.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_leads_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.leads.index'))
            ->assertOk();
    }

    public function test_super_admin_can_list_leads_via_data_endpoint(): void
    {
        $admin = $this->userWithRole('Super Admin');
        ContactLead::factory()->create(['name' => 'Jane Buyer']);

        $response = $this->actingAs($admin)->getJson(route('admin.leads.data'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Jane Buyer');
    }

    public function test_super_admin_can_update_lead_status(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $lead = ContactLead::factory()->create(['status' => 'new']);

        $this->actingAs($admin)
            ->putJson(route('admin.leads.update', $lead), ['status' => 'contacted'])
            ->assertOk();

        $this->assertDatabaseHas('contact_leads', ['id' => $lead->id, 'status' => 'contacted']);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $lead = ContactLead::factory()->create();

        $this->actingAs($admin)
            ->putJson(route('admin.leads.update', $lead), ['status' => 'bogus'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_super_admin_can_delete_lead(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $lead = ContactLead::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.leads.destroy', $lead))
            ->assertOk();

        $this->assertDatabaseMissing('contact_leads', ['id' => $lead->id]);
    }

    public function test_customer_role_cannot_delete_lead(): void
    {
        $customer = $this->userWithRole('B2B Customer');
        $lead = ContactLead::factory()->create();

        $this->actingAs($customer)
            ->deleteJson(route('admin.leads.destroy', $lead))
            ->assertForbidden();
    }
}
