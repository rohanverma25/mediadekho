<?php

namespace Tests\Feature\Api;

use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobApplicationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guest_can_submit_an_application_with_a_resume(): void
    {
        $job = Job::factory()->create(['status' => 'active']);

        $response = $this->postJson('/api/job-applications', [
            'job_id' => $job->id,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'phone' => '+91 90000 00000',
            'resume' => UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf'),
            'cover_letter' => 'I would love to join the team.',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'name' => 'Jane Applicant',
            'user_id' => null,
            'resume_original_name' => 'resume.pdf',
            'status' => 'new',
        ]);

        $application = \App\Models\JobApplication::query()->where('name', 'Jane Applicant')->firstOrFail();
        Storage::disk('public')->assertExists($application->resume);
    }

    public function test_application_without_a_resume_still_succeeds(): void
    {
        $job = Job::factory()->create(['status' => 'active']);

        $this->postJson('/api/job-applications', [
            'job_id' => $job->id,
            'name' => 'No Resume Applicant',
            'email' => 'noresume@example.com',
        ])->assertCreated();

        $this->assertDatabaseHas('job_applications', ['name' => 'No Resume Applicant', 'resume' => null]);
    }

    public function test_authenticated_user_submission_is_linked_to_their_account(): void
    {
        $job = Job::factory()->create(['status' => 'active']);
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/job-applications', [
                'job_id' => $job->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('job_applications', ['job_id' => $job->id, 'user_id' => $user->id]);
    }

    public function test_cannot_apply_to_an_inactive_job(): void
    {
        $job = Job::factory()->create(['status' => 'inactive']);

        $this->postJson('/api/job-applications', [
            'job_id' => $job->id,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors('job_id');
    }

    public function test_name_and_email_are_required(): void
    {
        $job = Job::factory()->create(['status' => 'active']);

        $this->postJson('/api/job-applications', ['job_id' => $job->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_non_document_resume_is_rejected(): void
    {
        $job = Job::factory()->create(['status' => 'active']);

        $this->postJson('/api/job-applications', [
            'job_id' => $job->id,
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'resume' => UploadedFile::fake()->image('not-a-resume.png'),
        ])->assertUnprocessable()->assertJsonValidationErrors('resume');
    }
}
