<?php

namespace Tests\Feature\Api;

use App\Models\Faq;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_unlinked_faqs(): void
    {
        Faq::factory()->create(['status' => 'active', 'question' => 'General Active']);
        Faq::factory()->create(['status' => 'inactive', 'question' => 'General Inactive']);

        $response = $this->getJson('/api/faqs');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.question', 'General Active')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'question', 'answer'],
                ],
            ]);
    }

    public function test_index_excludes_faqs_linked_to_a_category_or_inventory_item(): void
    {
        $category = MediaCategory::factory()->create();
        $inventory = MediaInventory::factory()->create();

        Faq::factory()->create(['status' => 'active', 'question' => 'General', 'category_id' => null, 'inventory_id' => null]);
        Faq::factory()->create(['status' => 'active', 'question' => 'Category Linked', 'category_id' => $category->id]);
        Faq::factory()->create(['status' => 'active', 'question' => 'Inventory Linked', 'inventory_id' => $inventory->id]);

        $response = $this->getJson('/api/faqs');

        $questions = collect($response->json('data'))->pluck('question');

        $response->assertOk();
        $this->assertTrue($questions->contains('General'));
        $this->assertFalse($questions->contains('Category Linked'));
        $this->assertFalse($questions->contains('Inventory Linked'));
    }

    public function test_index_orders_by_sort_order(): void
    {
        Faq::factory()->create(['status' => 'active', 'question' => 'Second', 'sort_order' => 2]);
        Faq::factory()->create(['status' => 'active', 'question' => 'First', 'sort_order' => 1]);

        $response = $this->getJson('/api/faqs');

        $response->assertOk()
            ->assertJsonPath('data.0.question', 'First')
            ->assertJsonPath('data.1.question', 'Second');
    }
}
