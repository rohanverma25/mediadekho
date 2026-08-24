<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_profile(): void
    {
        $this->putJson('/api/profile', ['name' => 'Nope', 'email' => 'nope@example.com'])
            ->assertUnauthorized();
    }

    public function test_user_can_update_name_email_phone_company(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '+91 90000 00000',
            'company' => 'Acme Co',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'New Name')
            ->assertJsonPath('email', 'new@example.com')
            ->assertJsonPath('phone', '+91 90000 00000');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'company' => 'Acme Co',
        ]);
    }

    public function test_email_uniqueness_excludes_the_current_user(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', ['name' => $user->name, 'email' => 'me@example.com'])
            ->assertOk();
    }

    public function test_email_must_be_unique_to_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', ['name' => $user->name, 'email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_blank_password_leaves_it_unchanged(): void
    {
        $user = User::factory()->create(['password' => 'original-password']);
        $originalHash = $user->password;

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', ['name' => $user->name, 'email' => $user->email])
            ->assertOk();

        $this->assertSame($originalHash, $user->refresh()->password);
    }

    public function test_provided_password_is_hashed_and_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertOk();

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('brand-new-password', $user->password));
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'brand-new-password',
                'password_confirmation' => 'does-not-match',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}
