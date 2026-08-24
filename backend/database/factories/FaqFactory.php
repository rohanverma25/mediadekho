<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => null,
            'inventory_id' => null,
            'question' => ucfirst($this->faker->sentence()).'?',
            'answer' => $this->faker->paragraph(),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
