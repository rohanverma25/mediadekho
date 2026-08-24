<?php

namespace Database\Factories;

use App\Models\Frequency;
use App\Models\Language;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaInventory>
 */
class MediaInventoryFactory extends Factory
{
    private const STATUSES = ['draft', 'published', 'archived'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => MediaCategory::factory(),
            'subcategory_id' => null,
            'frequency_id' => Frequency::factory(),
            'language_id' => Language::factory(),
            'title' => $this->faker->company().' — '.$this->faker->words(3, true),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(self::STATUSES),
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published']);
    }
}
