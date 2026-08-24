<?php

namespace Database\Factories;

use App\Models\Award;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Award>
 */
class AwardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->sentence(4)),
            'description' => '<p>'.$this->faker->paragraph().'</p>',
            'image' => null,
            'type' => 'upcoming',
            'organization' => $this->faker->company(),
            'event_date' => $this->faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'type' => 'past',
            'event_date' => $this->faker->dateTimeBetween('-3 years', '-1 month')->format('Y-m-d'),
        ]);
    }
}
