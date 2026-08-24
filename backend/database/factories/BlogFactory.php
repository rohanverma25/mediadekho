<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->sentence(6)),
            'excerpt' => $this->faker->sentence(20),
            'content' => '<p>'.$this->faker->paragraphs(3, true).'</p>',
            'featured_image' => null,
            'author_name' => $this->faker->name(),
            'status' => 'published',
            'published_at' => now(),
        ];
    }
}
