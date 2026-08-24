<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Deliberately NOT using WithoutModelEvents — MediaCategory/MediaInventory/
    // Blog all generate their slug via a creating() observer, and disabling
    // model events silently skips that, leaving slug NOT NULL columns unset.

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
            MediaInventorySeeder::class,
        ]);
    }
}
