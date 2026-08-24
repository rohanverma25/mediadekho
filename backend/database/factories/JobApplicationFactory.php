<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'user_id' => null,
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'resume' => null,
            'resume_original_name' => null,
            'cover_letter' => $this->faker->paragraph(),
            'status' => 'new',
        ];
    }
}
