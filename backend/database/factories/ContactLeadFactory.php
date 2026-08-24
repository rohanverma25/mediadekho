<?php

namespace Database\Factories;

use App\Models\ContactLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactLead>
 */
class ContactLeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company_name' => $this->faker->company(),
            'location' => $this->faker->city(),
            'subject' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => 'new',
        ];
    }
}
