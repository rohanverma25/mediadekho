<?php

namespace Tests\Feature\Api;

use App\Models\Award;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_awards_for_guests(): void
    {
        Award::factory()->create(['status' => 'active', 'title' => 'Active Award']);
        Award::factory()->create(['status' => 'inactive', 'title' => 'Inactive Award']);

        $response = $this->getJson('/api/awards');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Active Award')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'image_url', 'type', 'organization', 'event_date'],
                ],
            ]);
    }

    public function test_index_includes_both_upcoming_and_past_types(): void
    {
        Award::factory()->create(['status' => 'active', 'title' => 'Upcoming One', 'type' => 'upcoming']);
        Award::factory()->past()->create(['status' => 'active', 'title' => 'Past One']);

        $response = $this->getJson('/api/awards');

        $types = collect($response->json('data'))->pluck('type');

        $response->assertOk();
        $this->assertTrue($types->contains('upcoming'));
        $this->assertTrue($types->contains('past'));
    }
}
