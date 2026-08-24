<?php

namespace Tests\Feature\Api;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_published_posts_for_guests(): void
    {
        Blog::factory()->create(['status' => 'published', 'title' => 'Published Post']);
        Blog::factory()->create(['status' => 'draft', 'title' => 'Draft Post']);

        $response = $this->getJson('/api/blogs');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Published Post')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'excerpt', 'content', 'featured_image_url', 'author_name', 'published_at'],
                ],
            ]);
    }

    public function test_index_orders_newest_published_first(): void
    {
        Blog::factory()->create(['status' => 'published', 'title' => 'Older', 'published_at' => now()->subDays(5)]);
        Blog::factory()->create(['status' => 'published', 'title' => 'Newer', 'published_at' => now()]);

        $response = $this->getJson('/api/blogs');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Newer')
            ->assertJsonPath('data.1.title', 'Older');
    }

    public function test_show_returns_published_post_by_slug(): void
    {
        $blog = Blog::factory()->create(['status' => 'published', 'title' => 'A Detailed Guide To Billboards']);

        $response = $this->getJson("/api/blogs/{$blog->slug}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'A Detailed Guide To Billboards')
            ->assertJsonPath('data.slug', $blog->slug);
    }

    public function test_show_is_forbidden_for_draft_post_to_guests(): void
    {
        $blog = Blog::factory()->create(['status' => 'draft']);

        $this->getJson("/api/blogs/{$blog->slug}")->assertForbidden();
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/blogs/does-not-exist')->assertNotFound();
    }
}
