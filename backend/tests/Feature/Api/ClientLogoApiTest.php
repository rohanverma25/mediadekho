<?php

namespace Tests\Feature\Api;

use App\Models\ClientLogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientLogoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_logos_for_guests(): void
    {
        ClientLogo::factory()->count(2)->create(['status' => 'active']);
        ClientLogo::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/client-logos');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'logo_url', 'website_url'],
                ],
            ]);
    }

    public function test_index_orders_logos_by_sort_order(): void
    {
        $second = ClientLogo::factory()->create(['name' => 'Second', 'sort_order' => 2]);
        $first = ClientLogo::factory()->create(['name' => 'First', 'sort_order' => 1]);

        $response = $this->getJson('/api/client-logos');

        $response->assertOk()
            ->assertJsonPath('data.0.name', $first->name)
            ->assertJsonPath('data.1.name', $second->name);
    }
}
