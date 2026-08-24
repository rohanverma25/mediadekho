<?php

namespace Database\Factories;

use App\Models\Award;
use App\Models\AwardNomination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AwardNomination>
 */
class AwardNominationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'award_id' => Award::factory(),
            'user_id' => null,
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company_name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'status' => 'new',
        ];
    }
}
