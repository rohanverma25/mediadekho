<?php

namespace Tests\Feature\Api;

use App\Models\Frequency;
use App\Models\Language;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class MediaInventoryApiCrudTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_login_issues_a_token(): void
    {
        $user = $this->userWithRole('B2C Customer');
        $user->update(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'roles']]);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $user = $this->userWithRole();
        $user->update(['password' => bcrypt('password123')]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_unauthenticated_request_cannot_create_inventory(): void
    {
        $category = MediaCategory::factory()->create();
        $frequency = Frequency::factory()->create();
        $language = Language::factory()->create();

        $this->postJson('/api/media-inventory', [
            'category_id' => $category->id,
            'frequency_id' => $frequency->id,
            'language_id' => $language->id,
            'title' => 'Should Fail',
            'status' => 'draft',
        ])->assertUnauthorized();
    }

    public function test_customer_role_cannot_create_inventory_via_api(): void
    {
        $customer = $this->userWithRole('B2B Customer');
        $category = MediaCategory::factory()->create();
        $frequency = Frequency::factory()->create();
        $language = Language::factory()->create();

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/media-inventory', [
                'category_id' => $category->id,
                'frequency_id' => $frequency->id,
                'language_id' => $language->id,
                'title' => 'Should Fail',
                'status' => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_staff_can_create_inventory_via_api(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $frequency = Frequency::factory()->create();
        $language = Language::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/media-inventory', [
                'category_id' => $category->id,
                'frequency_id' => $frequency->id,
                'language_id' => $language->id,
                'title' => 'API Created Item',
                'status' => 'published',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'API Created Item');

        $this->assertDatabaseHas('media_inventory', ['title' => 'API Created Item']);
    }

    public function test_staff_can_update_inventory_via_api(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id, 'title' => 'Old Title']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/media-inventory/{$inventory->id}", [
                'title' => 'New Title',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'New Title');
    }

    public function test_staff_can_delete_inventory_via_api(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/media-inventory/{$inventory->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('media_inventory', ['id' => $inventory->id]);
    }

    public function test_search_index_only_returns_published_for_guests(): void
    {
        $category = MediaCategory::factory()->create();
        MediaInventory::factory()->published()->create(['category_id' => $category->id, 'title' => 'Published Item']);
        MediaInventory::factory()->create(['category_id' => $category->id, 'status' => 'draft', 'title' => 'Draft Item']);

        $response = $this->getJson('/api/media-inventory')->assertOk();

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertContains('Published Item', $titles);
        $this->assertNotContains('Draft Item', $titles);
    }

    public function test_show_exposes_seo_meta_fields(): void
    {
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->published()->create([
            'category_id' => $category->id,
            'meta_title' => 'Billboard on Main Street | Book Now',
            'meta_description' => 'Prime billboard site with high daily footfall.',
        ]);

        $this->getJson("/api/media-inventory/{$inventory->slug}")
            ->assertOk()
            ->assertJsonPath('data.meta_title', 'Billboard on Main Street | Book Now')
            ->assertJsonPath('data.meta_description', 'Prime billboard site with high daily footfall.');
    }

    public function test_search_index_can_filter_by_category(): void
    {
        $categoryA = MediaCategory::factory()->create();
        $categoryB = MediaCategory::factory()->create();
        MediaInventory::factory()->published()->create(['category_id' => $categoryA->id, 'title' => 'In Category A']);
        MediaInventory::factory()->published()->create(['category_id' => $categoryB->id, 'title' => 'In Category B']);

        $response = $this->getJson("/api/media-inventory?category_id={$categoryA->id}")->assertOk();

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertContains('In Category A', $titles);
        $this->assertNotContains('In Category B', $titles);
    }
}
