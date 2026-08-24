<?php

namespace Tests\Feature\Admin;

use App\Models\Frequency;
use App\Models\Language;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class MediaInventoryTest extends TestCase
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
        $this->get(route('admin.media-inventory.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_inventory_admin(): void
    {
        $customer = $this->userWithRole('B2C Customer');

        $this->actingAs($customer)
            ->get(route('admin.media-inventory.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_inventory(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $frequency = Frequency::factory()->create();
        $language = Language::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.media-inventory.store'), [
                'category_id' => $category->id,
                'frequency_id' => $frequency->id,
                'language_id' => $language->id,
                'title' => 'Billboard on Main Street',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('media_inventory', [
            'title' => 'Billboard on Main Street',
            'slug' => 'billboard-on-main-street',
        ]);
    }

    public function test_slug_regenerates_when_title_changes(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id, 'title' => 'Old Title']);

        $this->actingAs($admin)->put(route('admin.media-inventory.update', $inventory), [
            'category_id' => $category->id,
            'frequency_id' => $inventory->frequency_id,
            'language_id' => $inventory->language_id,
            'title' => 'Brand New Title',
            'status' => 'draft',
        ])->assertRedirect();

        $this->assertDatabaseHas('media_inventory', [
            'id' => $inventory->id,
            'slug' => 'brand-new-title',
        ]);
    }

    public function test_super_admin_can_set_pricing(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->post(route('admin.media-inventory.price.store', $inventory), [
            'base_price' => 1000,
            'retail_percentage' => 50,
            'b2c_percentage' => 40,
            'b2b_percentage' => 20,
        ])->assertRedirect();

        $this->assertDatabaseHas('media_inventory_prices', [
            'inventory_id' => $inventory->id,
            'base_price' => 1000,
            'retail_percentage' => 50,
            'retail_price' => 1500, // 1000 + 50%
            'b2c_price' => 1400, // 1000 + 40%
            'b2b_price' => 1200, // 1000 + 20%
        ]);
    }

    public function test_negative_percentage_is_rejected(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->post(route('admin.media-inventory.price.store', $inventory), [
            'base_price' => 1000,
            'retail_percentage' => 50,
            'b2c_percentage' => 40,
            'b2b_percentage' => -5, // negative markup isn't allowed
        ])->assertSessionHasErrors('b2b_percentage');
    }

    public function test_admin_role_cannot_manage_pricing(): void
    {
        $admin = $this->userWithRole('Admin'); // Admin has every permission except inventory.price.manage
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->post(route('admin.media-inventory.price.store', $inventory), [
            'base_price' => 1000,
            'retail_percentage' => 50,
            'b2c_percentage' => 40,
            'b2b_percentage' => 20,
        ])->assertForbidden();
    }

    public function test_super_admin_can_delete_inventory(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)
            ->delete(route('admin.media-inventory.destroy', $inventory))
            ->assertRedirect();

        $this->assertSoftDeleted('media_inventory', ['id' => $inventory->id]);
    }

    public function test_detail_page_shows_pricing_breakdown_and_audit_history(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);
        $inventory->price()->create([
            'base_price' => 1000,
            'retail_price' => 1500,
            'b2c_price' => 1400,
            'b2b_price' => 1200,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.media-inventory.show', $inventory))
            ->assertOk()
            ->assertSee('Pricing')
            ->assertSee('Audit History');
    }
}
