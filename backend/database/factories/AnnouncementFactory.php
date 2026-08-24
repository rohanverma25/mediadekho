<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->sentence(4)),
            'message' => $this->faker->paragraph(),
            'event_date' => $this->faker->optional()->dateTimeBetween('now', '+2 months')?->format('Y-m-d'),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
