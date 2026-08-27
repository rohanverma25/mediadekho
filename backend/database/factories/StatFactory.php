<?php

namespace Database\Factories;

use App\Models\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stat>
 */
class StatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => $this->faker->numberBetween(50, 5000).'+',
            'label' => ucfirst($this->faker->words(3, true)),
            'icon' => 'fa-solid fa-chart-simple',
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
