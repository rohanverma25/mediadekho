<?php

namespace Tests\Feature\Admin;

use App\Models\PageMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class PageMetaControllerTest extends TestCase
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
        $this->get(route('admin.page-meta.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_page_meta_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.page-meta.index'))
            ->assertForbidden();
    }

    /**
     * The create_page_metas_table migration seeds the fixed set of static
     * pages directly — this proves that seeding actually ran and the index
     * lists them, without depending on a separate seeder command.
     */
    public function test_index_lists_the_seeded_static_pages(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($admin)->get(route('admin.page-meta.index'));

        $response->assertOk();
        $response->assertSee('Homepage');
        $response->assertSee('Contact Us');
    }

    public function test_super_admin_can_edit_a_page_meta_entry(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $pageMeta = PageMeta::query()->where('page_key', 'contact')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.page-meta.update', $pageMeta), [
                'title' => 'Talk To Our Media Team',
                'description' => 'Reach out for campaign proposals and support.',
            ])
            ->assertRedirect(route('admin.page-meta.index'));

        $this->assertDatabaseHas('page_metas', [
            'page_key' => 'contact',
            'title' => 'Talk To Our Media Team',
            'description' => 'Reach out for campaign proposals and support.',
        ]);
    }

    public function test_super_admin_can_upload_an_og_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $pageMeta = PageMeta::query()->where('page_key', 'home')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.page-meta.update', $pageMeta), [
                'og_image' => UploadedFile::fake()->image('home-share.jpg'),
            ])
            ->assertRedirect();

        $pageMeta->refresh();
        Storage::disk('public')->assertExists($pageMeta->og_image);
    }

    public function test_replacing_an_og_image_deletes_the_old_one(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $pageMeta = PageMeta::query()->where('page_key', 'home')->firstOrFail();
        $pageMeta->update(['og_image' => 'page-meta/original.jpg']);
        Storage::disk('public')->put('page-meta/original.jpg', 'fake-content');

        $this->actingAs($admin)
            ->put(route('admin.page-meta.update', $pageMeta), [
                'og_image' => UploadedFile::fake()->image('new-share.jpg'),
            ])
            ->assertRedirect();

        $pageMeta->refresh();
        Storage::disk('public')->assertMissing('page-meta/original.jpg');
        Storage::disk('public')->assertExists($pageMeta->og_image);
    }

    public function test_customer_role_cannot_update_page_meta(): void
    {
        $customer = $this->userWithRole('B2B Customer');
        $pageMeta = PageMeta::query()->where('page_key', 'home')->firstOrFail();

        $this->actingAs($customer)
            ->put(route('admin.page-meta.update', $pageMeta), ['title' => 'Should Not Work'])
            ->assertForbidden();
    }
}
