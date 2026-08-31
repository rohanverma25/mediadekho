<?php

namespace Tests\Feature\Api;

use App\Models\ClientLogo;
use App\Models\Industry;
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

    /**
     * This is what makes clicking an industry on the homepage actually
     * filter the Clients page down to that industry's logos.
     */
    public function test_index_can_be_filtered_by_industry(): void
    {
        $foodDelivery = Industry::factory()->create(['title' => 'Food Delivery']);
        $fashion = Industry::factory()->create(['title' => 'Fashion']);
        ClientLogo::factory()->create(['name' => 'Swiggy', 'industry_id' => $foodDelivery->id, 'status' => 'active']);
        ClientLogo::factory()->create(['name' => 'Myntra', 'industry_id' => $fashion->id, 'status' => 'active']);

        $response = $this->getJson("/api/client-logos?industry_id={$foodDelivery->id}")->assertOk();

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Swiggy'));
        $this->assertFalse($names->contains('Myntra'));
    }

    public function test_index_exposes_the_industry_on_each_logo(): void
    {
        $industry = Industry::factory()->create(['title' => 'Food Delivery']);
        ClientLogo::factory()->create(['name' => 'Swiggy', 'industry_id' => $industry->id, 'status' => 'active']);

        $this->getJson('/api/client-logos')
            ->assertOk()
            ->assertJsonPath('data.0.industry.title', 'Food Delivery');
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
