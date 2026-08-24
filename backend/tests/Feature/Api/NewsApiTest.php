<?php

namespace Tests\Feature\Api;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_news_for_guests(): void
    {
        News::factory()->create(['status' => 'active', 'title' => 'Active News']);
        News::factory()->create(['status' => 'inactive', 'title' => 'Inactive News']);

        $response = $this->getJson('/api/news');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Active News')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'image_url', 'link'],
                ],
            ]);
    }

    public function test_index_orders_by_sort_order(): void
    {
        News::factory()->create(['status' => 'active', 'title' => 'Second', 'sort_order' => 2]);
        News::factory()->create(['status' => 'active', 'title' => 'First', 'sort_order' => 1]);

        $response = $this->getJson('/api/news');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'First')
            ->assertJsonPath('data.1.title', 'Second');
    }
}
