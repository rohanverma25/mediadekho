<?php

namespace Tests\Feature\Api;

use App\Models\Magazine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MagazineApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_active_magazines(): void
    {
        Magazine::factory()->create(['title' => 'Active Issue', 'status' => 'active']);
        Magazine::factory()->create(['title' => 'Inactive Issue', 'status' => 'inactive']);

        $response = $this->getJson('/api/magazines')->assertOk();

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertTrue($titles->contains('Active Issue'));
        $this->assertFalse($titles->contains('Inactive Issue'));
    }

    public function test_guest_can_view_a_single_active_magazine_by_slug(): void
    {
        $magazine = Magazine::factory()->create(['title' => 'August Issue', 'status' => 'active']);

        $this->getJson("/api/magazines/{$magazine->slug}")
            ->assertOk()
            ->assertJsonPath('data.title', 'August Issue')
            ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'description', 'cover_image_url', 'pdf_url', 'pdf_stream_url', 'published_at']]);
    }

    public function test_guest_cannot_view_an_inactive_magazine(): void
    {
        $magazine = Magazine::factory()->create(['status' => 'inactive']);

        $this->getJson("/api/magazines/{$magazine->slug}")
            ->assertForbidden();
    }

    /**
     * The in-browser reader loads the PDF via this route specifically
     * because /api/* gets CORS headers and the static /uploads/... file
     * host doesn't — assert the header is actually there, not just that
     * the file streams.
     */
    public function test_guest_can_stream_an_active_magazines_pdf_with_cors_header(): void
    {
        Storage::fake('public');
        $magazine = Magazine::factory()->create(['status' => 'active', 'pdf_file' => 'magazines/test.pdf']);
        Storage::disk('public')->put('magazines/test.pdf', '%PDF-1.4 fake content');

        $response = $this->get("/api/magazines/{$magazine->slug}/pdf");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotNull($response->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_guest_cannot_stream_an_inactive_magazines_pdf(): void
    {
        Storage::fake('public');
        $magazine = Magazine::factory()->create(['status' => 'inactive', 'pdf_file' => 'magazines/test.pdf']);
        Storage::disk('public')->put('magazines/test.pdf', '%PDF-1.4 fake content');

        $this->get("/api/magazines/{$magazine->slug}/pdf")
            ->assertForbidden();
    }
}
