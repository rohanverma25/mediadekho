<?php

namespace Database\Factories;

use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->jobTitle()),
            'description' => '<p>'.$this->faker->paragraph().'</p>',
            'department' => $this->faker->randomElement(['Sales', 'Marketing', 'Engineering', 'Operations']),
            'location' => $this->faker->randomElement(['Ahmedabad', 'Mumbai', 'Remote']),
            'type' => $this->faker->randomElement(['full-time', 'part-time', 'internship', 'contract']),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
