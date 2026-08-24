<?php

namespace Tests\Feature\Api;

use App\Models\ContactLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactLeadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_the_contact_form(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Jane Buyer',
            'email' => 'jane@example.com',
            'phone' => '+91 90000 00000',
            'company_name' => 'Acme Co',
            'location' => 'Mumbai',
            'subject' => 'Campaign Inquiry',
            'description' => 'We would like to plan a magazine campaign.',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('contact_leads', [
            'name' => 'Jane Buyer',
            'email' => 'jane@example.com',
            'subject' => 'Campaign Inquiry',
            'status' => 'new',
        ]);
    }

    public function test_name_email_and_description_are_required(): void
    {
        $response = $this->postJson('/api/contact', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'description']);
    }

    public function test_optional_fields_can_be_omitted(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Minimal Submitter',
            'email' => 'minimal@example.com',
            'description' => 'Just a quick question.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_leads', ['name' => 'Minimal Submitter']);
    }

    public function test_submitted_leads_are_never_publicly_readable(): void
    {
        ContactLead::factory()->create();

        // There is no public GET /api/contact — only the admin panel can
        // browse leads. Confirm no accidental public listing route exists.
        $this->getJson('/api/contact')->assertStatus(405);
    }
}
