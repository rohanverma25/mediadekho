<?php

namespace Tests\Feature\Api;

use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_includes_a_real_published_inventory_count_per_category(): void
    {
        $category = MediaCategory::factory()->create(['status' => 'active']);

        MediaInventory::factory()->count(2)->create(['category_id' => $category->id, 'status' => 'published']);
        MediaInventory::factory()->create(['category_id' => $category->id, 'status' => 'draft']);

        $response = $this->getJson('/api/media-categories');

        $response->assertOk()->assertJsonFragment(['id' => $category->id, 'inventory_count' => 2]);
    }

    public function test_show_includes_the_same_published_inventory_count(): void
    {
        $category = MediaCategory::factory()->create(['status' => 'active']);
        MediaInventory::factory()->create(['category_id' => $category->id, 'status' => 'published']);

        $this->getJson("/api/media-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.inventory_count', 1);
    }
}
