<?php

namespace Tests\Feature\Api;

use App\Mail\MediaListingRequestAdminNotification;
use App\Mail\MediaListingRequestCustomerConfirmation;
use App\Models\MediaListingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaListingRequestApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guest_can_submit_a_media_listing_request(): void
    {
        $response = $this->postJson('/api/media-listing-requests', [
            'company_name' => 'Acme Outdoor',
            'contact_name' => 'Jane Vendor',
            'email' => 'jane@acmeoutdoor.com',
            'phone' => '+91 90000 00000',
            'media_title' => 'Downtown Billboard',
            'media_type' => 'Hoarding',
            'location' => 'Mumbai',
            'approximate_rate' => '₹50,000/month',
            'description' => 'High-traffic billboard near the highway.',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('media_listing_requests', [
            'company_name' => 'Acme Outdoor',
            'media_title' => 'Downtown Billboard',
            'status' => 'new',
        ]);
    }

    public function test_a_photo_and_media_kit_can_be_uploaded(): void
    {
        $this->postJson('/api/media-listing-requests', [
            'company_name' => 'Acme Outdoor',
            'contact_name' => 'Jane Vendor',
            'email' => 'jane@acmeoutdoor.com',
            'phone' => '+91 90000 00000',
            'media_title' => 'Downtown Billboard',
            'image' => UploadedFile::fake()->image('billboard.jpg'),
            'media_kit' => UploadedFile::fake()->create('rate-card.pdf', 500, 'application/pdf'),
        ])->assertCreated();

        $request = MediaListingRequest::query()->where('media_title', 'Downtown Billboard')->firstOrFail();

        $this->assertSame('rate-card.pdf', $request->media_kit_original_name);
        Storage::disk('public')->assertExists($request->image);
        Storage::disk('public')->assertExists($request->media_kit);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/api/media-listing-requests', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['company_name', 'contact_name', 'email', 'phone', 'media_title']);
    }

    public function test_non_document_media_kit_is_rejected(): void
    {
        $this->postJson('/api/media-listing-requests', [
            'company_name' => 'Acme Outdoor',
            'contact_name' => 'Jane Vendor',
            'email' => 'jane@acmeoutdoor.com',
            'phone' => '+91 90000 00000',
            'media_title' => 'Downtown Billboard',
            'media_kit' => UploadedFile::fake()->create('malware.exe', 100),
        ])->assertUnprocessable()->assertJsonValidationErrors('media_kit');
    }

    public function test_submitting_sends_admin_and_customer_emails(): void
    {
        Mail::fake();

        $this->postJson('/api/media-listing-requests', [
            'company_name' => 'Acme Outdoor',
            'contact_name' => 'Jane Vendor',
            'email' => 'jane@acmeoutdoor.com',
            'phone' => '+91 90000 00000',
            'media_title' => 'Downtown Billboard',
        ])->assertCreated();

        Mail::assertSent(MediaListingRequestAdminNotification::class);
        Mail::assertSent(MediaListingRequestCustomerConfirmation::class, fn ($mail) => $mail->hasTo('jane@acmeoutdoor.com'));
    }
}
