<?php

namespace Tests\Feature\Admin;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class JobControllerTest extends TestCase
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
        $this->get(route('admin.jobs.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_jobs_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.jobs.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_jobs_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.jobs.index'))
            ->assertOk();
    }

    public function test_super_admin_can_create_job(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.jobs.store'), [
                'title' => 'Senior Media Planner',
                'description' => '<p>Plan and execute large-scale media campaigns.</p>',
                'department' => 'Sales',
                'location' => 'Ahmedabad',
                'type' => 'full-time',
                'status' => 'active',
                'sort_order' => 1,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('jobs_board', [
            'title' => 'Senior Media Planner',
            'department' => 'Sales',
            'type' => 'full-time',
        ]);
    }

    public function test_creating_job_without_a_title_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.jobs.store'), ['type' => 'full-time', 'status' => 'active'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_creating_job_with_invalid_type_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.jobs.store'), ['title' => 'Bad Type', 'type' => 'bogus', 'status' => 'active'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }

    public function test_customer_role_cannot_create_job(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.jobs.store'), ['title' => 'Should Not Work', 'type' => 'full-time', 'status' => 'active'])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_job(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $job = Job::factory()->create(['title' => 'Original Title']);

        $this->actingAs($admin)
            ->putJson(route('admin.jobs.update', $job), [
                'title' => 'Renamed Role',
                'type' => $job->type,
                'status' => 'inactive',
            ])
            ->assertOk();

        $this->assertDatabaseHas('jobs_board', ['id' => $job->id, 'title' => 'Renamed Role', 'status' => 'inactive']);
    }

    public function test_super_admin_can_delete_job(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $job = Job::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.jobs.destroy', $job))
            ->assertOk();

        $this->assertDatabaseMissing('jobs_board', ['id' => $job->id]);
    }

    public function test_deleting_job_cascades_its_applications(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $job = Job::factory()->create();
        JobApplication::factory()->create(['job_id' => $job->id]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.jobs.destroy', $job))
            ->assertOk();

        $this->assertDatabaseMissing('job_applications', ['job_id' => $job->id]);
    }
}
