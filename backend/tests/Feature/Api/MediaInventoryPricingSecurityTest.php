<?php

namespace Tests\Feature\Api;

use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

/**
 * The spec's core security requirement: "Never expose other prices. Even if
 * someone changes the API URL manually, the backend must return only the
 * allowed price." This class exists to prove that guarantee holds under
 * PHPUnit, not just via the manual curl checks used during development.
 */
class MediaInventoryPricingSecurityTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    private MediaInventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();

        $category = MediaCategory::factory()->create();
        $this->inventory = MediaInventory::factory()->published()->create(['category_id' => $category->id]);
        $this->inventory->price()->create([
            'base_price' => 1000,
            'retail_price' => 1500,
            'b2c_price' => 1400,
            'b2b_price' => 1200,
            'enterprise_price' => 1050,
        ]);
    }

    /**
     * Pricing is a logged-in-only feature — a guest gets a locked response
     * with no numeric price anywhere in it, not even the retail rate.
     */
    public function test_guest_receives_no_price(): void
    {
        $response = $this->getJson("/api/media-inventory/{$this->inventory->slug}/price")
            ->assertOk()
            ->assertJson(['available' => false, 'locked' => true]);

        $response->assertJsonMissingPath('price');
        $response->assertJsonMissingPath('final_price');
        $response->assertJsonMissingPath('list_price');
    }

    public function test_b2c_customer_receives_only_b2c_price(): void
    {
        $user = $this->userWithRole('B2C Customer');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/media-inventory/{$this->inventory->slug}/price")
            ->assertOk()
            ->assertJson(['tier' => 'b2c', 'price' => 1400.0]);

        $body = $response->json();
        $this->assertNotEquals(1500.0, $body['price']); // not retail
        $this->assertNotEquals(1200.0, $body['price']); // not b2b
        $this->assertNotEquals(1050.0, $body['price']); // not enterprise
    }

    public function test_b2b_customer_receives_only_b2b_price(): void
    {
        $user = $this->userWithRole('B2B Customer');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/media-inventory/{$this->inventory->slug}/price")
            ->assertOk()
            ->assertJson(['tier' => 'b2b', 'price' => 1200.0]);
    }

    public function test_enterprise_customer_receives_only_enterprise_price(): void
    {
        $user = $this->userWithRole('Enterprise Customer');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/media-inventory/{$this->inventory->slug}/price")
            ->assertOk()
            ->assertJson(['tier' => 'enterprise', 'price' => 1050.0]);
    }

    /**
     * The literal spec requirement: URL/query tampering must not change
     * which tier is returned. The API never reads a tier from the request
     * at all — but this test proves that's actually true, not assumed.
     */
    public function test_query_string_tampering_does_not_change_resolved_tier(): void
    {
        $user = $this->userWithRole('B2C Customer');

        foreach ([
            '?tier=b2b',
            '?tier=enterprise',
            '?role=b2b',
            '?price_tier=enterprise&force_tier=b2b',
        ] as $tampering) {
            $this->actingAs($user, 'sanctum')
                ->getJson("/api/media-inventory/{$this->inventory->slug}/price{$tampering}")
                ->assertOk()
                ->assertJson(['tier' => 'b2c', 'price' => 1400.0]);
        }
    }

    public function test_embedded_price_in_detail_endpoint_also_respects_tier_isolation(): void
    {
        $user = $this->userWithRole('B2B Customer');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/media-inventory/{$this->inventory->slug}")
            ->assertOk()
            ->assertJsonPath('data.price.tier', 'b2b');

        $this->assertEquals(1200.0, $response->json('data.price.price'));
    }

    public function test_customer_facing_payload_never_contains_internal_financial_fields(): void
    {
        $user = $this->userWithRole('B2B Customer');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/media-inventory/{$this->inventory->slug}/price")
            ->assertOk();

        $response->assertJsonMissingPath('commission_amount');
        $response->assertJsonMissingPath('net_profit');
        $response->assertJsonMissingPath('platform_margin');
        $response->assertJsonMissingPath('base_price');
    }

    public function test_draft_inventory_is_hidden_from_guests_and_customers(): void
    {
        $draft = MediaInventory::factory()->create([
            'category_id' => $this->inventory->category_id,
            'status' => 'draft',
        ]);
        $customer = $this->userWithRole('B2C Customer');

        $this->getJson("/api/media-inventory/{$draft->slug}")->assertForbidden();
        $this->actingAs($customer, 'sanctum')
            ->getJson("/api/media-inventory/{$draft->slug}")
            ->assertForbidden();
    }

    public function test_draft_inventory_is_visible_to_staff_with_inventory_view_permission(): void
    {
        $draft = MediaInventory::factory()->create([
            'category_id' => $this->inventory->category_id,
            'status' => 'draft',
        ]);
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/media-inventory/{$draft->slug}")
            ->assertOk();
    }
}
