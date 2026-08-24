<?php

namespace Tests\Feature\Admin;

use App\Models\Award;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class AwardControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.awards.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_awards_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.awards.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_awards_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.awards.index'))
            ->assertOk();
    }

    public function test_super_admin_can_create_upcoming_award(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.awards.store'), [
                'title' => 'Best Media Platform 2027',
                'description' => '<p>Nominate your favourite campaign.</p>',
                'type' => 'upcoming',
                'organization' => 'IAA India',
                'event_date' => '2027-03-15',
                'image' => UploadedFile::fake()->image('award.jpg'),
                'status' => 'active',
                'sort_order' => 1,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('awards', [
            'title' => 'Best Media Platform 2027',
            'type' => 'upcoming',
            'organization' => 'IAA India',
        ]);

        $award = Award::query()->where('title', 'Best Media Platform 2027')->firstOrFail();
        Storage::disk('public')->assertExists($award->image);
    }

    public function test_creating_award_without_a_title_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.awards.store'), [
                'type' => 'upcoming',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_creating_award_with_invalid_type_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.awards.store'), [
                'title' => 'Bad Type',
                'type' => 'bogus',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }

    public function test_customer_role_cannot_create_award(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.awards.store'), [
                'title' => 'Should Not Work',
                'type' => 'upcoming',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_award_without_replacing_the_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $award = Award::factory()->create(['title' => 'Original Title', 'image' => 'awards/original.jpg']);

        $this->actingAs($admin)
            ->putJson(route('admin.awards.update', $award), [
                'title' => 'Renamed Award',
                'type' => $award->type,
                'status' => 'inactive',
            ])
            ->assertOk();

        $this->assertDatabaseHas('awards', [
            'id' => $award->id,
            'title' => 'Renamed Award',
            'status' => 'inactive',
            'image' => 'awards/original.jpg',
        ]);
    }

    public function test_super_admin_can_delete_award(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $award = Award::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.awards.destroy', $award))
            ->assertOk();

        $this->assertDatabaseMissing('awards', ['id' => $award->id]);
    }

    public function test_deleting_award_cascades_its_nominations(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $award = Award::factory()->create();
        \App\Models\AwardNomination::factory()->create(['award_id' => $award->id]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.awards.destroy', $award))
            ->assertOk();

        $this->assertDatabaseMissing('award_nominations', ['award_id' => $award->id]);
    }
}
