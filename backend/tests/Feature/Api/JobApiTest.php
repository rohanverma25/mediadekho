<?php

namespace Tests\Feature\Api;

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_jobs_for_guests(): void
    {
        Job::factory()->create(['status' => 'active', 'title' => 'Active Job']);
        Job::factory()->create(['status' => 'inactive', 'title' => 'Inactive Job']);

        $response = $this->getJson('/api/jobs');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Active Job')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'department', 'location', 'type'],
                ],
            ]);
    }

    public function test_index_orders_by_sort_order(): void
    {
        Job::factory()->create(['status' => 'active', 'title' => 'Second', 'sort_order' => 2]);
        Job::factory()->create(['status' => 'active', 'title' => 'First', 'sort_order' => 1]);

        $response = $this->getJson('/api/jobs');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'First')
            ->assertJsonPath('data.1.title', 'Second');
    }
}
