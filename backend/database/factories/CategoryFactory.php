<?php

namespace Database\Factories;

use App\Helpers\SlugHelper;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => SlugHelper::unique(Category::class, $name),
            'description' => $this->faker->sentence(),
            'youtube_video_link' => null,
            'image' => null,
            'main_image' => null,
        ];
    }
}
