<?php

namespace Tests\Feature\Admin;

use App\Models\MediaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class MediaCategoryTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_category_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_category_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk();
    }

    /**
     * The Media Categories table is for top-level categories only —
     * sub-categories have their own dedicated page/table and must not also
     * show up here once created.
     */
    public function test_category_listing_excludes_subcategories(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = MediaCategory::factory()->create(['name' => 'Parent Category']);
        $child = MediaCategory::factory()->child($parent)->create(['name' => 'Child Subcategory']);

        $response = $this->actingAs($admin)->getJson(route('admin.categories.data'));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Parent Category'));
        $this->assertFalse($names->contains('Child Subcategory'));
    }

    public function test_super_admin_can_create_category_with_auto_generated_slug(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.categories.store'), [
                'name' => 'Outdoor Advertising',
                'status' => 'active',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('media_categories', [
            'name' => 'Outdoor Advertising',
            'slug' => 'outdoor-advertising',
        ]);
    }

    public function test_creating_category_with_duplicate_name_still_gets_unique_slug(): void
    {
        $admin = $this->userWithRole('Super Admin');
        MediaCategory::factory()->create(['name' => 'Digital Media']);

        // Force the observer's slug generator by creating via the same name
        $this->actingAs($admin)->postJson(route('admin.categories.store'), [
            'name' => 'Digital Media',
            'status' => 'active',
        ])->assertCreated();

        $this->assertDatabaseHas('media_categories', ['slug' => 'digital-media']);
        $this->assertDatabaseHas('media_categories', ['slug' => 'digital-media-1']);
    }

    public function test_super_admin_can_flag_a_category_for_homepage_and_popular(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.categories.store'), [
                'name' => 'Airport Advertising',
                'status' => 'active',
                'show_on_homepage' => '1',
                'show_on_popular' => '1',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('media_categories', [
            'name' => 'Airport Advertising',
            'show_on_homepage' => 1,
            'show_on_popular' => 1,
        ]);
    }

    /**
     * Checkbox inputs are simply absent from the payload when unchecked —
     * the controller must still coerce that to an explicit false rather
     * than leaving the flag untouched.
     */
    public function test_omitting_visibility_checkboxes_saves_them_as_false(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.categories.store'), [
                'name' => 'Radio Advertising',
                'status' => 'active',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('media_categories', [
            'name' => 'Radio Advertising',
            'show_on_homepage' => 0,
            'show_on_popular' => 0,
        ]);
    }

    public function test_super_admin_can_toggle_visibility_flags_off_on_update(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create([
            'show_on_homepage' => true,
            'show_on_popular' => true,
        ]);

        $this->actingAs($admin)
            ->putJson(route('admin.categories.update', $category), [
                'name' => $category->name,
                'status' => 'active',
            ])
            ->assertOk();

        $this->assertDatabaseHas('media_categories', [
            'id' => $category->id,
            'show_on_homepage' => 0,
            'show_on_popular' => 0,
        ]);
    }

    public function test_super_admin_can_set_seo_meta_fields_on_a_category(): void
    {
        $admin = $this->userWithRole('Super Admin');
        Storage::fake('public');

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Outdoor Advertising',
                'status' => 'active',
                'meta_title' => 'Outdoor Advertising Rates in India',
                'meta_description' => 'Compare billboard and hoarding rates across India.',
                'meta_image' => UploadedFile::fake()->image('outdoor-meta.jpg'),
            ])
            ->assertCreated();

        $category = MediaCategory::query()->where('name', 'Outdoor Advertising')->firstOrFail();

        $this->assertSame('Outdoor Advertising Rates in India', $category->meta_title);
        $this->assertSame('Compare billboard and hoarding rates across India.', $category->meta_description);
        Storage::disk('public')->assertExists($category->meta_image);
    }

    public function test_super_admin_can_update_category(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create(['name' => 'Original Name']);

        $this->actingAs($admin)
            ->putJson(route('admin.categories.update', $category), [
                'name' => 'Renamed Category',
                'status' => 'active',
            ])
            ->assertOk();

        $this->assertDatabaseHas('media_categories', [
            'id' => $category->id,
            'name' => 'Renamed Category',
            'slug' => 'renamed-category',
        ]);
    }

    public function test_super_admin_can_delete_category(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.categories.destroy', $category))
            ->assertOk();

        $this->assertSoftDeleted('media_categories', ['id' => $category->id]);
    }

    public function test_admin_role_can_also_manage_categories(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk();
    }

    /**
     * parent_id is no longer editable from this form, but the column and
     * relationship still exist in the database (e.g. from before this
     * change, or via Media Inventory's sub-category assignment) — deleting
     * a category that's still a parent must stay blocked so it doesn't
     * orphan those rows.
     */
    public function test_category_with_children_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = MediaCategory::factory()->create();
        MediaCategory::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.categories.destroy', $parent))
            ->assertUnprocessable();

        $this->assertDatabaseHas('media_categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_childless_category_can_be_deleted(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.categories.destroy', $category))
            ->assertOk();

        $this->assertSoftDeleted('media_categories', ['id' => $category->id]);
    }
}
