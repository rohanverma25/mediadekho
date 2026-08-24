<?php

namespace Tests\Feature\Admin;

use App\Models\MediaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
