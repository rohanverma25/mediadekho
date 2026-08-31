<?php

namespace Tests\Feature\Api;

use App\Mail\PasswordResetEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_a_reset_email_for_an_existing_account(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'rajesh@techcorp.com']);

        $this->postJson('/api/forgot-password', ['email' => 'rajesh@techcorp.com'])
            ->assertOk();

        Mail::assertSent(PasswordResetEmail::class, function (PasswordResetEmail $mail) use ($user) {
            return $mail->hasTo($user->email) && str_contains($mail->resetUrl, 'token=') && str_contains($mail->resetUrl, urlencode($user->email));
        });
    }

    /**
     * The response must be identical whether or not the email exists —
     * otherwise this endpoint becomes a way to enumerate registered users.
     */
    public function test_forgot_password_returns_the_same_generic_response_for_an_unknown_email(): void
    {
        Mail::fake();

        $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Mail::assertNothingSent();
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('brand-new-password', $user->refresh()->password));
    }

    public function test_reset_password_rejects_an_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/reset-password', [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_reset_password_requires_matching_confirmation(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'does-not-match',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_reset_token_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'first-new-password',
            'password_confirmation' => 'first-new-password',
        ])->assertOk();

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'second-new-password',
            'password_confirmation' => 'second-new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_user_can_log_in_with_the_new_password_after_reset(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertOk();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'brand-new-password',
        ])->assertOk()->assertJsonStructure(['token']);
    }
}
