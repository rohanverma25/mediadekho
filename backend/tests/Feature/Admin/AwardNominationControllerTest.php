<?php

namespace Tests\Feature\Admin;

use App\Models\Award;
use App\Models\AwardNomination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class AwardNominationControllerTest extends TestCase
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
        $this->get(route('admin.award-nominations.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_nominations_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.award-nominations.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_nominations_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.award-nominations.index'))
            ->assertOk();
    }

    public function test_super_admin_can_list_nominations_with_award_title(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $award = Award::factory()->create(['title' => 'Best Campaign 2027']);
        AwardNomination::factory()->create(['award_id' => $award->id, 'name' => 'Jane Nominee']);

        $response = $this->actingAs($admin)->getJson(route('admin.award-nominations.data'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Jane Nominee')
            ->assertJsonPath('data.0.award_title', 'Best Campaign 2027');
    }

    public function test_super_admin_can_update_nomination_status(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $nomination = AwardNomination::factory()->create(['status' => 'new']);

        $this->actingAs($admin)
            ->putJson(route('admin.award-nominations.update', $nomination), ['status' => 'shortlisted'])
            ->assertOk();

        $this->assertDatabaseHas('award_nominations', ['id' => $nomination->id, 'status' => 'shortlisted']);
    }

    public function test_super_admin_can_delete_nomination(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $nomination = AwardNomination::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.award-nominations.destroy', $nomination))
            ->assertOk();

        $this->assertDatabaseMissing('award_nominations', ['id' => $nomination->id]);
    }
}
