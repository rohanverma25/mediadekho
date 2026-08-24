<?php

namespace Database\Factories;

use App\Models\MediaCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaCategory>
 */
class MediaCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Slug is intentionally omitted — MediaCategoryObserver generates it
     * from `name` on creating(), the same path real requests go through.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'parent_id' => null,
            'name' => $name,
            'description' => $this->faker->sentence(),
            'icon' => 'heroicon-o-'.Str::slug($this->faker->word()),
            'status' => 'active',
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function child(MediaCategory $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }
}
