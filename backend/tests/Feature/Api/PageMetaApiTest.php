<?php

namespace Tests\Feature\Api;

use App\Models\PageMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageMetaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_fetch_meta_for_a_known_page(): void
    {
        $this->getJson('/api/page-meta/home')
            ->assertOk()
            ->assertJsonPath('data.page_key', 'home');
    }

    public function test_unknown_page_key_returns_404(): void
    {
        $this->getJson('/api/page-meta/not-a-real-page')
            ->assertNotFound();
    }

    public function test_returns_admin_edited_title_description_and_image(): void
    {
        $pageMeta = PageMeta::query()->where('page_key', 'faq')->firstOrFail();
        $pageMeta->update([
            'title' => 'Got Questions?',
            'description' => 'Everything you need to know before booking media.',
            'og_image' => 'page-meta/faq.jpg',
        ]);

        $response = $this->getJson('/api/page-meta/faq')->assertOk();

        $response->assertJsonPath('data.title', 'Got Questions?');
        $response->assertJsonPath('data.description', 'Everything you need to know before booking media.');
        $this->assertNotNull($response->json('data.og_image_url'));
    }
}
