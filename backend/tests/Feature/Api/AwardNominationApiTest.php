<?php

namespace Tests\Feature\Api;

use App\Models\Award;
use App\Models\AwardNomination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardNominationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_nomination(): void
    {
        $award = Award::factory()->create(['status' => 'active', 'type' => 'upcoming']);

        $response = $this->postJson('/api/award-nominations', [
            'award_id' => $award->id,
            'name' => 'Jane Nominee',
            'email' => 'jane@example.com',
            'phone' => '+91 90000 00000',
            'company_name' => 'Acme Co',
            'description' => 'Nominating our campaign for its reach and creativity.',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('award_nominations', [
            'award_id' => $award->id,
            'name' => 'Jane Nominee',
            'user_id' => null,
            'status' => 'new',
        ]);
    }

    public function test_authenticated_user_submission_is_linked_to_their_account(): void
    {
        $award = Award::factory()->create(['status' => 'active', 'type' => 'upcoming']);
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/award-nominations', [
                'award_id' => $award->id,
                'name' => $user->name,
                'email' => $user->email,
                'description' => 'Nominating myself.',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('award_nominations', [
            'award_id' => $award->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_cannot_nominate_for_a_past_award(): void
    {
        $award = Award::factory()->past()->create(['status' => 'active']);

        $this->postJson('/api/award-nominations', [
            'award_id' => $award->id,
            'name' => 'Jane Nominee',
            'email' => 'jane@example.com',
            'description' => 'Should fail.',
        ])->assertUnprocessable()->assertJsonValidationErrors('award_id');
    }

    public function test_cannot_nominate_for_an_inactive_award(): void
    {
        $award = Award::factory()->create(['status' => 'inactive', 'type' => 'upcoming']);

        $this->postJson('/api/award-nominations', [
            'award_id' => $award->id,
            'name' => 'Jane Nominee',
            'email' => 'jane@example.com',
            'description' => 'Should fail.',
        ])->assertUnprocessable()->assertJsonValidationErrors('award_id');
    }

    public function test_name_email_and_description_are_required(): void
    {
        $award = Award::factory()->create(['status' => 'active', 'type' => 'upcoming']);

        $this->postJson('/api/award-nominations', ['award_id' => $award->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'description']);
    }

    public function test_guest_cannot_view_my_nominations(): void
    {
        $this->getJson('/api/my/award-nominations')->assertUnauthorized();
    }

    public function test_authenticated_user_only_sees_their_own_nominations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $award = Award::factory()->create(['title' => 'My Award']);

        AwardNomination::factory()->create(['award_id' => $award->id, 'user_id' => $user->id, 'description' => 'Mine']);
        AwardNomination::factory()->create(['award_id' => $award->id, 'user_id' => $otherUser->id, 'description' => 'Not mine']);
        AwardNomination::factory()->create(['award_id' => $award->id, 'user_id' => null, 'description' => 'Guest submission']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/my/award-nominations');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.subject', 'Mine')
            ->assertJsonPath('data.0.award.title', 'My Award');
    }
}
