<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_fetch_settings(): void
    {
        Setting::current()->update([
            'contact_phone' => '+91 89800 04451',
            'contact_email' => 'inquiry@mediadekho.com',
            'facebook_url' => 'https://www.facebook.com/MediaDekho',
            'privacy_policy' => '<p>We respect your privacy.</p>',
            'terms_of_use' => '<p>Terms go here.</p>',
            'about_us' => '<p>Media Dekho is India\'s largest media aggregator.</p>',
            'contact_emails' => [['title' => 'Sales', 'email' => 'sales@mediadekho.com']],
            'contact_addresses' => [['title' => 'Head Office', 'address' => 'Ahmedabad HQ']],
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=abc123',
        ]);

        $response = $this->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'logo_url', 'contact_phone', 'contact_email', 'contact_address',
                    'footer_description', 'social' => ['facebook', 'instagram', 'linkedin', 'youtube'],
                    'header_scripts', 'footer_scripts', 'privacy_policy', 'terms_of_use', 'about_us',
                    'contact_emails', 'contact_addresses', 'map_embed_url',
                ],
            ])
            ->assertJsonPath('data.contact_phone', '+91 89800 04451')
            ->assertJsonPath('data.social.facebook', 'https://www.facebook.com/MediaDekho')
            ->assertJsonPath('data.privacy_policy', '<p>We respect your privacy.</p>')
            ->assertJsonPath('data.terms_of_use', '<p>Terms go here.</p>')
            ->assertJsonPath('data.about_us', '<p>Media Dekho is India\'s largest media aggregator.</p>')
            ->assertJsonPath('data.contact_emails.0.title', 'Sales')
            ->assertJsonPath('data.contact_emails.0.email', 'sales@mediadekho.com')
            ->assertJsonPath('data.contact_addresses.0.title', 'Head Office')
            ->assertJsonPath('data.map_embed_url', 'https://www.google.com/maps/embed?pb=abc123');
    }

    public function test_contact_emails_and_addresses_default_to_empty_arrays(): void
    {
        Setting::current();

        $response = $this->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonPath('data.contact_emails', [])
            ->assertJsonPath('data.contact_addresses', []);
    }

    public function test_settings_endpoint_works_even_if_row_was_deleted(): void
    {
        Setting::query()->delete();

        // firstOrCreate() recreates the row, so JsonResource reports 201
        // (Laravel's "was this model just created" convention) rather than
        // 200 — either way, the point is it doesn't error.
        $this->getJson('/api/settings')->assertSuccessful();
    }
}
