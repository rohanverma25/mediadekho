<?php

namespace Tests\Unit;

use App\Models\MediaCategory;
use App\Models\MediaInventory;
use App\Models\MediaInventoryPrice;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        $this->pricing = app(PricingService::class);
    }

    private function makeInventoryWithPrice(array $priceOverrides = []): MediaInventory
    {
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);

        $price = MediaInventoryPrice::factory()->create(array_merge([
            'inventory_id' => $inventory->id,
            'base_price' => 1000,
            'retail_price' => 1500,
            'b2c_price' => 1400,
            'b2b_price' => 1200,
            'enterprise_price' => 1050,
            'discount_type' => null,
            'discount_value' => null,
            'tax_percentage' => 0,
            'commission_percentage' => 0,
            'platform_margin' => 0,
        ], $priceOverrides));

        $inventory->setRelation('price', $price);

        return $inventory;
    }

    public function test_resolve_tier_defaults_to_retail_for_guest(): void
    {
        $this->assertSame(PricingService::TIER_RETAIL, $this->pricing->resolveTier(null));
    }

    public function test_resolve_tier_defaults_to_retail_for_user_without_role(): void
    {
        $user = $this->userWithRole();

        $this->assertSame(PricingService::TIER_RETAIL, $this->pricing->resolveTier($user));
    }

    public function test_resolve_tier_matches_b2c_role(): void
    {
        $user = $this->userWithRole('B2C Customer');

        $this->assertSame(PricingService::TIER_B2C, $this->pricing->resolveTier($user));
    }

    public function test_resolve_tier_matches_b2b_role(): void
    {
        $user = $this->userWithRole('B2B Customer');

        $this->assertSame(PricingService::TIER_B2B, $this->pricing->resolveTier($user));
    }

    public function test_resolve_tier_matches_enterprise_role(): void
    {
        $user = $this->userWithRole('Enterprise Customer');

        $this->assertSame(PricingService::TIER_ENTERPRISE, $this->pricing->resolveTier($user));
    }

    /**
     * Pricing is a logged-in-only feature — a guest gets a locked response
     * with no numeric price, tier, or breakdown fields at all.
     */
    public function test_price_for_user_returns_locked_response_for_guest(): void
    {
        $inventory = $this->makeInventoryWithPrice();

        $result = $this->pricing->priceForUser($inventory, null);

        $this->assertSame(['tier' => null, 'available' => false, 'locked' => true], $result);
    }

    public function test_price_for_user_returns_correct_tier_price(): void
    {
        $inventory = $this->makeInventoryWithPrice();

        $retail = $this->pricing->priceForUser($inventory, $this->userWithRole('Retail Customer'));
        $b2c = $this->pricing->priceForUser($inventory, $this->userWithRole('B2C Customer'));
        $b2b = $this->pricing->priceForUser($inventory, $this->userWithRole('B2B Customer'));
        $enterprise = $this->pricing->priceForUser($inventory, $this->userWithRole('Enterprise Customer'));

        $this->assertSame(1500.0, $retail['price']);
        $this->assertSame(1400.0, $b2c['price']);
        $this->assertSame(1200.0, $b2b['price']);
        $this->assertSame(1050.0, $enterprise['price']);
    }

    /**
     * The core security guarantee: the customer-facing payload must never
     * contain any tier other than the caller's own, nor any internal
     * financial figures (commission, net profit, platform margin).
     */
    public function test_price_for_user_never_leaks_other_tiers_or_internal_figures(): void
    {
        $inventory = $this->makeInventoryWithPrice();
        $b2cUser = $this->userWithRole('B2C Customer');

        $result = $this->pricing->priceForUser($inventory, $b2cUser);

        $this->assertSame(
            ['tier', 'available', 'locked', 'list_price', 'discount_amount', 'price', 'tax_percentage', 'tax_amount', 'final_price'],
            array_keys($result)
        );
        $this->assertSame(PricingService::TIER_B2C, $result['tier']);

        $forbiddenValues = [1500.0, 1200.0, 1050.0]; // retail, b2b, enterprise prices
        $this->assertNotContains($result['price'], $forbiddenValues);
        $this->assertNotContains($result['list_price'], $forbiddenValues);
    }

    public function test_price_for_user_falls_back_to_b2b_when_enterprise_price_unset(): void
    {
        $inventory = $this->makeInventoryWithPrice(['enterprise_price' => null]);
        $enterpriseUser = $this->userWithRole('Enterprise Customer');

        $result = $this->pricing->priceForUser($inventory, $enterpriseUser);

        $this->assertSame(1200.0, $result['price']);
    }

    public function test_price_for_user_reports_unavailable_when_no_price_configured(): void
    {
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create(['category_id' => $category->id]);
        $inventory->setRelation('price', null);

        $result = $this->pricing->priceForUser($inventory, $this->userWithRole('Retail Customer'));

        $this->assertFalse($result['available']);
        $this->assertFalse($result['locked']);
    }

    public function test_flat_discount_is_subtracted_from_every_tier(): void
    {
        $inventory = $this->makeInventoryWithPrice(['discount_type' => 'flat', 'discount_value' => 100]);

        $retail = $this->pricing->priceForUser($inventory, $this->userWithRole('Retail Customer'));

        $this->assertSame(1400.0, $retail['price']); // 1500 - 100
    }

    public function test_percentage_discount_is_calculated_against_base_price(): void
    {
        $inventory = $this->makeInventoryWithPrice(['discount_type' => 'percentage', 'discount_value' => 10]);

        // 10% of base_price (1000) = 100 discount, applied flat to every tier
        $retail = $this->pricing->priceForUser($inventory, $this->userWithRole('Retail Customer'));

        $this->assertSame(1400.0, $retail['price']); // 1500 - 100
    }

    public function test_tax_is_applied_on_top_of_selling_price(): void
    {
        $inventory = $this->makeInventoryWithPrice(['tax_percentage' => 18]);

        $retail = $this->pricing->priceForUser($inventory, $this->userWithRole('Retail Customer'));

        $this->assertSame(270.0, $retail['tax_amount']); // 1500 * 18%
        $this->assertSame(1770.0, $retail['final_price']); // 1500 + 270
    }

    public function test_admin_breakdown_exposes_all_tiers_and_financials(): void
    {
        $inventory = $this->makeInventoryWithPrice([
            'tax_percentage' => 18,
            'commission_percentage' => 5,
            'platform_margin' => 50,
        ]);

        $breakdown = $this->pricing->adminBreakdown($inventory->price);

        $this->assertSame(1000.0, $breakdown['base_price']);
        $this->assertArrayHasKey('retail', $breakdown['tiers']);
        $this->assertArrayHasKey('b2c', $breakdown['tiers']);
        $this->assertArrayHasKey('b2b', $breakdown['tiers']);
        $this->assertArrayHasKey('enterprise', $breakdown['tiers']);

        $retail = $breakdown['tiers']['retail'];
        $this->assertSame(1500.0, $retail['selling_price']); // no discount configured
        $this->assertSame(270.0, $retail['tax_amount']); // 1500 * 18%
        $this->assertSame(75.0, $retail['commission_amount']); // 1500 * 5%
        // net_profit = selling_price - base_price - commission - platform_margin
        // 1500 - 1000 - 75 - 50 = 375
        $this->assertSame(375.0, $retail['net_profit']);
        $this->assertSame(37.5, $retail['margin_percentage']); // 375 / 1000 * 100
    }

    public function test_admin_breakdown_reveals_negative_margin_when_costs_exceed_selling_price(): void
    {
        $inventory = $this->makeInventoryWithPrice([
            'base_price' => 1000,
            'b2b_price' => 1000, // equal to base, no room for costs
            'commission_percentage' => 20,
            'platform_margin' => 100,
        ]);

        $breakdown = $this->pricing->adminBreakdown($inventory->price);

        // selling_price=1000, commission=200, net_profit = 1000-1000-200-100 = -300
        $this->assertSame(-300.0, $breakdown['tiers']['b2b']['net_profit']);
        $this->assertTrue($breakdown['tiers']['b2b']['net_profit'] < 0);
    }

    public function test_admin_breakdown_null_tier_when_price_not_set(): void
    {
        $inventory = $this->makeInventoryWithPrice(['enterprise_price' => null]);

        $breakdown = $this->pricing->adminBreakdown($inventory->price);

        $this->assertNull($breakdown['tiers']['enterprise']);
    }
}
