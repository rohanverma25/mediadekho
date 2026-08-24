<?php

namespace Database\Factories;

use App\Models\MediaInventory;
use App\Models\MediaInventoryImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaInventoryImage>
 */
class MediaInventoryImageFactory extends Factory
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
            'path' => 'media-inventory/'.Str::uuid().'.jpg',
            'is_cover' => false,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function cover(): static
    {
        return $this->state(fn () => ['is_cover' => true, 'sort_order' => 0]);
    }
}
