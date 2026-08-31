<?php

namespace Tests\Feature\Admin;

use App\Models\MediaListingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class MediaListingRequestControllerTest extends TestCase
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
        $this->get(route('admin.media-listing-requests.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_media_listing_requests_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.media-listing-requests.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_media_listing_requests_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.media-listing-requests.index'))
            ->assertOk();
    }

    public function test_super_admin_can_list_media_listing_requests(): void
    {
        $admin = $this->userWithRole('Super Admin');
        MediaListingRequest::factory()->create(['media_title' => 'Downtown Billboard', 'company_name' => 'Acme Outdoor']);

        $response = $this->actingAs($admin)->getJson(route('admin.media-listing-requests.data'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.media_title', 'Downtown Billboard')
            ->assertJsonPath('data.0.company_name', 'Acme Outdoor');
    }

    public function test_super_admin_can_update_request_status(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $request = MediaListingRequest::factory()->create(['status' => 'new']);

        $this->actingAs($admin)
            ->putJson(route('admin.media-listing-requests.update', $request), ['status' => 'contacted'])
            ->assertOk();

        $this->assertDatabaseHas('media_listing_requests', ['id' => $request->id, 'status' => 'contacted']);
    }

    public function test_super_admin_can_delete_a_request(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $request = MediaListingRequest::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.media-listing-requests.destroy', $request))
            ->assertOk();

        $this->assertDatabaseMissing('media_listing_requests', ['id' => $request->id]);
    }
}
