<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->sentence(8)),
            'image' => 'news/'.$this->faker->uuid().'.jpg',
            'link' => $this->faker->url(),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
