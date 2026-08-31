<?php

namespace Database\Factories;

use App\Models\MediaListingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaListingRequest>
 */
class MediaListingRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
            'media_title' => ucfirst($this->faker->words(3, true)),
            'media_type' => $this->faker->randomElement(['Hoarding', 'Magazine', 'Radio', 'Digital Screen']),
            'location' => $this->faker->city(),
            'approximate_rate' => '₹'.$this->faker->numberBetween(5000, 200000).'/month',
            'description' => $this->faker->sentence(15),
            'status' => 'new',
        ];
    }
}
