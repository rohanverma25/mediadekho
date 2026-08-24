<?php

namespace Tests\Feature\Admin;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class JobApplicationControllerTest extends TestCase
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
        $this->get(route('admin.job-applications.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_applications_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.job-applications.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_applications_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.job-applications.index'))
            ->assertOk();
    }

    public function test_super_admin_can_list_applications_with_job_title(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $job = Job::factory()->create(['title' => 'Senior Media Planner']);
        JobApplication::factory()->create(['job_id' => $job->id, 'name' => 'Jane Applicant']);

        $response = $this->actingAs($admin)->getJson(route('admin.job-applications.data'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Jane Applicant')
            ->assertJsonPath('data.0.job_title', 'Senior Media Planner');
    }

    public function test_super_admin_can_update_application_status(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $application = JobApplication::factory()->create(['status' => 'new']);

        $this->actingAs($admin)
            ->putJson(route('admin.job-applications.update', $application), ['status' => 'shortlisted'])
            ->assertOk();

        $this->assertDatabaseHas('job_applications', ['id' => $application->id, 'status' => 'shortlisted']);
    }

    public function test_super_admin_can_delete_application(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $application = JobApplication::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.job-applications.destroy', $application))
            ->assertOk();

        $this->assertDatabaseMissing('job_applications', ['id' => $application->id]);
    }
}
