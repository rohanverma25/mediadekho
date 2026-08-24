<?php

namespace App\Services;

use App\Models\MediaInventory;
use App\Models\MediaInventoryPrice;
use App\Models\User;

class PricingService
{
    public const TIER_RETAIL = 'retail';

    public const TIER_B2C = 'b2c';

    public const TIER_B2B = 'b2b';

    public const TIER_ENTERPRISE = 'enterprise';

    /**
     * Determine which price tier a user is entitled to see. Unauthenticated
     * requests always fall back to Retail — the public rate.
     */
    public function resolveTier(?User $user): string
    {
        if (! $user) {
            return self::TIER_RETAIL;
        }

        return match (true) {
            $user->hasRole('Enterprise Customer') => self::TIER_ENTERPRISE,
            $user->hasRole('B2B Customer') => self::TIER_B2B,
            $user->hasRole('B2C Customer') => self::TIER_B2C,
            default => self::TIER_RETAIL,
        };
    }

    /**
     * Customer-facing price for a given user. This is the ONLY method
     * controllers/API resources should call to display a price to a
     * customer — it deliberately returns nothing beyond the tier and what
     * the customer pays. Callers must never serialize MediaInventoryPrice
     * directly, or every tier (and internal margin/commission/net-profit
     * figures) would leak regardless of the requester's role or what tier
     * they query for in the URL.
     */
    public function priceForUser(MediaInventory $inventory, ?User $user): array
    {
        $price = $inventory->price;

        if (! $price) {
            return ['tier' => $this->resolveTier($user), 'available' => false];
        }

        $tier = $this->resolveTier($user);
        $tierPrice = $this->tierPrice($price, $tier);
        $discountAmount = $this->discountAmount($price, (float) $price->base_price);
        $sellingPrice = round($tierPrice - $discountAmount, 2);
        $taxAmount = round($sellingPrice * ((float) $price->tax_percentage / 100), 2);

        return [
            'tier' => $tier,
            'available' => true,
            // list_price/discount_amount are the pre-discount tier rate and
            // what got knocked off it — safe to expose (unlike base_price,
            // commission, or margin) since they only describe what the
            // customer themselves is being charged, not platform economics.
            'list_price' => round($tierPrice, 2),
            'discount_amount' => $discountAmount,
            'price' => $sellingPrice,
            'tax_percentage' => (float) $price->tax_percentage,
            'tax_amount' => $taxAmount,
            'final_price' => round($sellingPrice + $taxAmount, 2),
        ];
    }

    /**
     * Full internal breakdown (all tiers, discount, tax, commission,
     * platform margin, net profit) for admin/staff use only. Callers are
     * responsible for gating this behind the `inventory.price.manage`
     * permission — this method itself has no auth awareness.
     */
    public function adminBreakdown(MediaInventoryPrice $price): array
    {
        $basePrice = (float) $price->base_price;
        $discountAmount = $this->discountAmount($price, $basePrice);

        $percentages = [
            self::TIER_RETAIL => (float) $price->retail_percentage,
            self::TIER_B2C => (float) $price->b2c_percentage,
            self::TIER_B2B => (float) $price->b2b_percentage,
        ];

        $tiers = [];

        foreach ([
            self::TIER_RETAIL => $price->retail_price,
            self::TIER_B2C => $price->b2c_price,
            self::TIER_B2B => $price->b2b_price,
            self::TIER_ENTERPRISE => $price->enterprise_price,
        ] as $tier => $tierPrice) {
            $tiers[$tier] = $tierPrice === null
                ? null
                : $this->calculateTier((float) $tierPrice, $basePrice, $discountAmount, $price) + ['markup_percentage' => $percentages[$tier] ?? null];
        }

        return [
            'base_price' => $basePrice,
            'discount_type' => $price->discount_type,
            'discount_value' => $price->discount_value === null ? null : (float) $price->discount_value,
            'discount_amount' => $discountAmount,
            'tax_percentage' => (float) $price->tax_percentage,
            'commission_percentage' => (float) $price->commission_percentage,
            'platform_margin' => (float) $price->platform_margin,
            'tiers' => $tiers,
        ];
    }

    /**
     * Selling price / tax / commission / net-profit math for a single tier.
     * Mirrors the formula the admin form's JS calculator uses, so the two
     * never disagree:
     *
     *   discount_amount = flat ? discount_value : base_price * discount_value%
     *   selling_price   = tier_price - discount_amount
     *   tax_amount      = selling_price * tax_percentage%
     *   final_price     = selling_price + tax_amount        (what the buyer pays)
     *   commission      = selling_price * commission_percentage%
     *   net_profit      = selling_price - base_price - commission - platform_margin
     */
    private function calculateTier(float $tierPrice, float $basePrice, float $discountAmount, MediaInventoryPrice $price): array
    {
        $sellingPrice = round($tierPrice - $discountAmount, 2);
        $taxAmount = round($sellingPrice * ((float) $price->tax_percentage / 100), 2);
        $commissionAmount = round($sellingPrice * ((float) $price->commission_percentage / 100), 2);
        $netProfit = round($sellingPrice - $basePrice - $commissionAmount - (float) $price->platform_margin, 2);

        return [
            'price' => $tierPrice,
            'selling_price' => $sellingPrice,
            'tax_amount' => $taxAmount,
            'final_price' => round($sellingPrice + $taxAmount, 2),
            'commission_amount' => $commissionAmount,
            'net_profit' => $netProfit,
            'margin_percentage' => $basePrice > 0 ? round(($netProfit / $basePrice) * 100, 2) : 0.0,
        ];
    }

    private function tierPrice(MediaInventoryPrice $price, string $tier): float
    {
        return (float) match ($tier) {
            self::TIER_ENTERPRISE => $price->enterprise_price ?? $price->b2b_price,
            self::TIER_B2B => $price->b2b_price,
            self::TIER_B2C => $price->b2c_price,
            default => $price->retail_price,
        };
    }

    private function discountAmount(MediaInventoryPrice $price, float $basePrice): float
    {
        if (! $price->discount_type || $price->discount_value === null) {
            return 0.0;
        }

        return round($price->discount_type === 'percentage'
            ? $basePrice * ((float) $price->discount_value / 100)
            : (float) $price->discount_value, 2);
    }
}
