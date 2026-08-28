<?php

namespace Tests\Feature\Api;

use App\Models\Faq;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_includes_a_real_published_inventory_count_per_category(): void
    {
        $category = MediaCategory::factory()->create(['status' => 'active']);

        MediaInventory::factory()->count(2)->create(['category_id' => $category->id, 'status' => 'published']);
        MediaInventory::factory()->create(['category_id' => $category->id, 'status' => 'draft']);

        $response = $this->getJson('/api/media-categories');

        $response->assertOk()->assertJsonFragment(['id' => $category->id, 'inventory_count' => 2]);
    }

    public function test_show_includes_the_same_published_inventory_count(): void
    {
        $category = MediaCategory::factory()->create(['status' => 'active']);
        MediaInventory::factory()->create(['category_id' => $category->id, 'status' => 'published']);

        $this->getJson("/api/media-categories/{$category->slug}")
            ->assertOk()
            ->assertJsonPath('data.inventory_count', 1);
    }

    /**
     * FAQs are scoped per category — a category's detail response must only
     * ever include its own linked FAQs, never another category's or the
     * unlinked "general" ones the homepage/FAQ page show.
     */
    public function test_show_includes_only_this_categorys_own_faqs(): void
    {
        $category = MediaCategory::factory()->create(['status' => 'active']);
        $otherCategory = MediaCategory::factory()->create(['status' => 'active']);

        Faq::factory()->create(['category_id' => $category->id, 'question' => 'Mine', 'status' => 'active']);
        Faq::factory()->create(['category_id' => $otherCategory->id, 'question' => 'Not mine', 'status' => 'active']);
        Faq::factory()->create(['category_id' => null, 'inventory_id' => null, 'question' => 'General', 'status' => 'active']);
        Faq::factory()->create(['category_id' => $category->id, 'question' => 'Inactive', 'status' => 'inactive']);

        $response = $this->getJson("/api/media-categories/{$category->slug}")->assertOk();

        $questions = collect($response->json('data.faqs'))->pluck('question');

        $this->assertTrue($questions->contains('Mine'));
        $this->assertFalse($questions->contains('Not mine'));
        $this->assertFalse($questions->contains('General'));
        $this->assertFalse($questions->contains('Inactive'));
    }
}
