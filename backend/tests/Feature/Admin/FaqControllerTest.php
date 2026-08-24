<?php

namespace Tests\Feature\Admin;

use App\Models\MediaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class FaqControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    /**
     * The "Link To: Category" dropdown on the FAQ form should only offer
     * top-level categories — a subcategory isn't something an FAQ should
     * link to as if it were a category.
     */
    public function test_category_dropdown_excludes_subcategories(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = MediaCategory::factory()->create(['name' => 'Parent Category']);
        MediaCategory::factory()->child($parent)->create(['name' => 'Child Subcategory']);

        $response = $this->actingAs($admin)->get(route('admin.faqs.index'));

        $response->assertOk();
        $response->assertViewHas('categories', function ($categories) {
            $names = $categories->pluck('name');

            return $names->contains('Parent Category') && ! $names->contains('Child Subcategory');
        });
    }
}
