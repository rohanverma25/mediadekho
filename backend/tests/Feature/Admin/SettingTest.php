<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class SettingTest extends TestCase
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
        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_settings_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_settings_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    /**
     * The migration always seeds the singleton row, so this simulates the
     * row having been deleted somehow — Setting::current()'s firstOrCreate()
     * fallback should still leave the page usable rather than 500ing.
     */
    public function test_settings_index_auto_creates_the_singleton_row_if_missing(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Setting::query()->delete();

        $this->assertDatabaseCount('settings', 0);

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk();

        $this->assertDatabaseCount('settings', 1);
    }

    public function test_super_admin_can_update_contact_info_and_footer_details(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Setting::current();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'contact_phone' => '+91 90000 11111',
                'contact_email' => 'new-contact@mediadekho.com',
                'contact_address' => 'New HQ Address, Mumbai',
                'footer_description' => 'Updated footer blurb.',
                'facebook_url' => 'https://www.facebook.com/NewPage',
            ])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'contact_phone' => '+91 90000 11111',
            'contact_email' => 'new-contact@mediadekho.com',
            'facebook_url' => 'https://www.facebook.com/NewPage',
        ]);
    }

    public function test_super_admin_can_update_multiple_emails_and_addresses_and_map(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Setting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'contact_emails' => [
                ['title' => 'Sales', 'email' => 'sales@mediadekho.com'],
                ['title' => 'Support', 'email' => 'support@mediadekho.com'],
            ],
            'contact_addresses' => [
                ['title' => 'Head Office', 'address' => 'Ahmedabad HQ Address'],
                ['title' => 'Mumbai Branch', 'address' => 'Mumbai Branch Address'],
            ],
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=abc123',
        ])->assertRedirect();

        $setting = Setting::current()->fresh();
        $this->assertSame([
            ['title' => 'Sales', 'email' => 'sales@mediadekho.com'],
            ['title' => 'Support', 'email' => 'support@mediadekho.com'],
        ], $setting->contact_emails);
        $this->assertSame([
            ['title' => 'Head Office', 'address' => 'Ahmedabad HQ Address'],
            ['title' => 'Mumbai Branch', 'address' => 'Mumbai Branch Address'],
        ], $setting->contact_addresses);
        $this->assertSame('https://www.google.com/maps/embed?pb=abc123', $setting->map_embed_url);
    }

    public function test_blank_email_and_address_repeater_rows_are_dropped(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Setting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'contact_emails' => [
                ['title' => '', 'email' => ''],
                ['title' => 'Sales', 'email' => 'sales@mediadekho.com'],
            ],
            'contact_addresses' => [
                ['title' => '', 'address' => ''],
            ],
        ])->assertRedirect();

        $setting = Setting::current()->fresh();
        $this->assertSame([['title' => 'Sales', 'email' => 'sales@mediadekho.com']], $setting->contact_emails);
        $this->assertSame([], $setting->contact_addresses);
    }

    public function test_super_admin_can_update_scripts(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Setting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'header_scripts' => '<script>console.log("ga")</script>',
            'footer_scripts' => '<script>console.log("chat")</script>',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'header_scripts' => '<script>console.log("ga")</script>',
            'footer_scripts' => '<script>console.log("chat")</script>',
        ]);
    }

    public function test_super_admin_can_upload_a_logo(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Setting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect();

        $setting = Setting::current();
        Storage::disk('public')->assertExists($setting->logo);
    }

    public function test_super_admin_can_update_legal_pages(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Setting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'privacy_policy' => '<p>We respect your privacy.</p>',
            'terms_of_use' => '<p>By using this site you agree to these terms.</p>',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'privacy_policy' => '<p>We respect your privacy.</p>',
            'terms_of_use' => '<p>By using this site you agree to these terms.</p>',
        ]);
    }

    public function test_customer_role_cannot_update_settings(): void
    {
        $customer = $this->userWithRole('B2B Customer');
        Setting::current();

        $this->actingAs($customer)
            ->put(route('admin.settings.update'), ['contact_phone' => '+91 99999 99999'])
            ->assertForbidden();
    }
}
