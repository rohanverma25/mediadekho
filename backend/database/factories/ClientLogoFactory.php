<?php

namespace Database\Factories;

use App\Models\ClientLogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientLogo>
 */
class ClientLogoFactory extends Factory
{
    protected $model = ClientLogo::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'logo' => 'client-logos/'.$this->faker->uuid().'.png',
            'website_url' => $this->faker->url(),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
