<?php

namespace Tests\Feature\Api;

use App\Models\ContactLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactLeadOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_submission_has_no_user_id(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Jane Guest',
            'email' => 'jane@example.com',
            'description' => 'Just a guest enquiry.',
        ])->assertCreated();

        $this->assertDatabaseHas('contact_leads', [
            'name' => 'Jane Guest',
            'user_id' => null,
        ]);
    }

    public function test_authenticated_submission_is_linked_to_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/contact', [
                'name' => $user->name,
                'email' => $user->email,
                'description' => 'Enquiring as a logged-in customer.',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('contact_leads', [
            'email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_view_my_enquiries(): void
    {
        $this->getJson('/api/my/contact-leads')->assertUnauthorized();
    }

    public function test_authenticated_user_only_sees_their_own_enquiries(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ContactLead::factory()->create(['user_id' => $user->id, 'subject' => 'Mine']);
        ContactLead::factory()->create(['user_id' => $otherUser->id, 'subject' => 'Not mine']);
        ContactLead::factory()->create(['user_id' => null, 'subject' => 'Guest submission']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/my/contact-leads');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.subject', 'Mine');
    }
}
