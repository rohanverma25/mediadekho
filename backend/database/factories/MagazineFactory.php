<?php

namespace Database\Factories;

use App\Models\Magazine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Magazine>
 */
class MagazineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->words(3, true)).' Issue',
            'description' => $this->faker->sentence(15),
            'cover_image' => null,
            'pdf_file' => 'magazines/placeholder.pdf',
            'published_at' => now(),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
