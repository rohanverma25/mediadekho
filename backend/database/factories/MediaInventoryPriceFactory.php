<?php

namespace Database\Factories;

use App\Models\MediaInventory;
use App\Models\MediaInventoryPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaInventoryPrice>
 */
class MediaInventoryPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Mirrors the spec's example tiering: Retail is the highest (public/walk-in
     * rate), B2C sits below it, B2B gets the deepest standing discount, and
     * Enterprise is left nullable since it's negotiated per-account.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $basePrice = $this->faker->randomFloat(2, 500, 5000);

        return [
            'inventory_id' => MediaInventory::factory(),
            'base_price' => $basePrice,
            'retail_percentage' => 50,
            'b2c_percentage' => 40,
            'b2b_percentage' => 20,
            'retail_price' => round($basePrice * 1.5, 2),
            'b2c_price' => round($basePrice * 1.4, 2),
            'b2b_price' => round($basePrice * 1.2, 2),
            'enterprise_price' => $this->faker->boolean(50) ? round($basePrice * 1.1, 2) : null,
            'discount_type' => $this->faker->randomElement([null, 'flat', 'percentage']),
            'discount_value' => $this->faker->randomFloat(2, 0, 15),
            'tax_percentage' => $this->faker->randomElement([0, 5, 12, 18]),
            'commission_percentage' => $this->faker->randomFloat(2, 2, 10),
            'platform_margin' => round($basePrice * 0.1, 2),
            'effective_from' => now(),
            'effective_to' => now()->addMonths(6),
            'status' => 'active',
        ];
    }
}
