<?php

namespace Database\Factories;

use App\Models\Frequency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Frequency>
 */
class FrequencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
        ];
    }
}
