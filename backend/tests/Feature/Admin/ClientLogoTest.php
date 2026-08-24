<?php

namespace Tests\Feature\Admin;

use App\Models\ClientLogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class ClientLogoTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.client-logos.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_client_logo_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.client-logos.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_client_logo_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.client-logos.index'))
            ->assertOk();
    }

    public function test_super_admin_can_create_client_logo(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.client-logos.store'), [
                'name' => 'Swiggy',
                'website_url' => 'https://www.swiggy.com',
                'logo' => UploadedFile::fake()->image('swiggy.png'),
                'status' => 'active',
                'sort_order' => 1,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('client_logos', [
            'name' => 'Swiggy',
            'website_url' => 'https://www.swiggy.com',
        ]);

        $logo = ClientLogo::query()->where('name', 'Swiggy')->firstOrFail();
        Storage::disk('public')->assertExists($logo->logo);
    }

    public function test_creating_client_logo_without_an_image_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.client-logos.store'), [
                'name' => 'Swiggy',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('logo');
    }

    public function test_customer_role_cannot_create_client_logo(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.client-logos.store'), [
                'name' => 'Swiggy',
                'logo' => UploadedFile::fake()->image('swiggy.png'),
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_client_logo_without_replacing_the_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $logo = ClientLogo::factory()->create(['name' => 'Original Name', 'logo' => 'client-logos/original.png']);

        $this->actingAs($admin)
            ->putJson(route('admin.client-logos.update', $logo), [
                'name' => 'Renamed Client',
                'status' => 'inactive',
                'sort_order' => 5,
            ])
            ->assertOk();

        $this->assertDatabaseHas('client_logos', [
            'id' => $logo->id,
            'name' => 'Renamed Client',
            'status' => 'inactive',
            'logo' => 'client-logos/original.png',
        ]);
    }

    public function test_super_admin_can_replace_client_logo_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $logo = ClientLogo::factory()->create(['logo' => 'client-logos/original.png']);
        Storage::disk('public')->put('client-logos/original.png', 'fake-contents');

        $this->actingAs($admin)
            ->putJson(route('admin.client-logos.update', $logo), [
                'name' => $logo->name,
                'status' => 'active',
                'logo' => UploadedFile::fake()->image('new-logo.png'),
            ])
            ->assertOk();

        $logo->refresh();
        Storage::disk('public')->assertMissing('client-logos/original.png');
        Storage::disk('public')->assertExists($logo->logo);
    }

    public function test_super_admin_can_delete_client_logo(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $logo = ClientLogo::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.client-logos.destroy', $logo))
            ->assertOk();

        $this->assertDatabaseMissing('client_logos', ['id' => $logo->id]);
    }
}
