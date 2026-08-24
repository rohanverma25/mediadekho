<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class AnnouncementTest extends TestCase
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
        $this->get(route('admin.announcements.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_announcement_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.announcements.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_announcement_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.announcements.index'))
            ->assertOk();
    }

    public function test_super_admin_can_create_announcement(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.announcements.store'), [
                'title' => 'Diwali Campaign Rush',
                'message' => 'Book your Diwali campaigns before 15th October to lock in pre-festive rates.',
                'event_date' => '2026-10-15',
                'status' => 'active',
                'sort_order' => 1,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('announcements', ['title' => 'Diwali Campaign Rush']);
        $this->assertSame('2026-10-15', Announcement::query()->where('title', 'Diwali Campaign Rush')->firstOrFail()->event_date->format('Y-m-d'));
    }

    public function test_customer_role_cannot_create_announcement(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.announcements.store'), [
                'title' => 'Should Fail',
                'message' => 'Should not be created.',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_announcement(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $announcement = Announcement::factory()->create(['title' => 'Original Title']);

        $this->actingAs($admin)
            ->putJson(route('admin.announcements.update', $announcement), [
                'title' => 'Renamed Announcement',
                'message' => $announcement->message,
                'status' => 'inactive',
            ])
            ->assertOk();

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Renamed Announcement',
            'status' => 'inactive',
        ]);
    }

    public function test_super_admin_can_delete_announcement(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $announcement = Announcement::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.announcements.destroy', $announcement))
            ->assertOk();

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }
}
