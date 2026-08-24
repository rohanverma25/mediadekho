<?php

namespace Tests\Feature\Api;

use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_announcements_for_guests(): void
    {
        Announcement::factory()->create(['status' => 'active', 'title' => 'Active One']);
        Announcement::factory()->create(['status' => 'inactive', 'title' => 'Inactive One']);

        $response = $this->getJson('/api/announcements');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Active One')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'message', 'event_date', 'created_at'],
                ],
            ]);
    }

    public function test_index_excludes_events_whose_date_has_passed(): void
    {
        Announcement::factory()->create(['status' => 'active', 'title' => 'Past Event', 'event_date' => now()->subDay()->toDateString()]);
        Announcement::factory()->create(['status' => 'active', 'title' => 'Future Event', 'event_date' => now()->addDay()->toDateString()]);
        Announcement::factory()->create(['status' => 'active', 'title' => 'General Announcement', 'event_date' => null]);

        $response = $this->getJson('/api/announcements');

        $titles = collect($response->json('data'))->pluck('title');

        $response->assertOk();
        $this->assertTrue($titles->contains('Future Event'));
        $this->assertTrue($titles->contains('General Announcement'));
        $this->assertFalse($titles->contains('Past Event'));
    }
}
