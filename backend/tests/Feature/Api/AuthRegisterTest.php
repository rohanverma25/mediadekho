<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class AuthRegisterTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    /**
     * B2B is agency-tier pricing, so unlike every other account type it
     * doesn't get a token back immediately — it's created 'pending' and
     * needs an admin to approve it before the account can log in.
     */
    public function test_guest_can_register_as_a_b2b_customer_but_gets_no_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Rajesh Kumar',
            'email' => 'rajesh@techcorp.com',
            'phone' => '+91 98765 43210',
            'company' => 'TechCorp Solutions',
            'password' => 'password123',
            'user_type' => 'b2b',
        ]);

        $response->assertCreated()
            ->assertJsonPath('pending_approval', true)
            ->assertJsonMissing(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'rajesh@techcorp.com',
            'phone' => '+91 98765 43210',
            'company' => 'TechCorp Solutions',
            'approval_status' => 'pending',
        ]);

        $user = User::query()->where('email', 'rajesh@techcorp.com')->firstOrFail();
        $this->assertTrue($user->hasRole('B2B Customer'));
    }

    public function test_pending_b2b_customer_cannot_log_in(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Rajesh Kumar',
            'email' => 'rajesh@techcorp.com',
            'password' => 'password123',
            'user_type' => 'b2b',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'email' => 'rajesh@techcorp.com',
            'password' => 'password123',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_b2b_customer_can_log_in_once_approved(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Rajesh Kumar',
            'email' => 'rajesh@techcorp.com',
            'password' => 'password123',
            'user_type' => 'b2b',
        ])->assertCreated();

        User::query()->where('email', 'rajesh@techcorp.com')->update(['approval_status' => 'approved']);

        $this->postJson('/api/login', [
            'email' => 'rajesh@techcorp.com',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('user.roles.0', 'B2B Customer');
    }

    #[DataProvider('userTypeProvider')]
    public function test_each_user_type_maps_to_its_customer_role(string $userType, string $expectedRole): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => "{$userType}@example.com",
            'password' => 'password123',
            'user_type' => $userType,
        ]);

        $response->assertCreated()->assertJsonPath('user.roles.0', $expectedRole);
    }

    public static function userTypeProvider(): array
    {
        return [
            'retail' => ['retail', 'Retail Customer'],
            'b2c' => ['b2c', 'B2C Customer'],
            'enterprise' => ['enterprise', 'Enterprise Customer'],
        ];
    }

    public function test_registration_rejects_an_invalid_user_type(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'invalid-type@example.com',
            'password' => 'password123',
            'user_type' => 'staff',
        ])->assertUnprocessable()->assertJsonValidationErrors('user_type');
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'user_type' => 'retail',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_registration_rejects_a_short_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'short-pw@example.com',
            'password' => 'short',
            'user_type' => 'retail',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_registered_user_can_immediately_log_in(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'login-after-register@example.com',
            'password' => 'password123',
            'user_type' => 'enterprise',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'email' => 'login-after-register@example.com',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('user.roles.0', 'Enterprise Customer');
    }
}
