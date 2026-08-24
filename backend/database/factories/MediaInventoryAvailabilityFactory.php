<?php

namespace Database\Factories;

use App\Models\MediaInventory;
use App\Models\MediaInventoryAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaInventoryAvailability>
 */
class MediaInventoryAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_id' => MediaInventory::factory(),
            'date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => $this->faker->randomElement(['available', 'booked', 'blocked']),
            'note' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }
}
