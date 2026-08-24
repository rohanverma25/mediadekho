<?php

namespace Tests\Feature\Admin;

use App\Models\MediaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class DashboardTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    /**
     * The "Categories" stat card should count top-level categories only —
     * subcategories aren't categories in their own right and shouldn't
     * inflate this number.
     */
    public function test_categories_stat_excludes_subcategories(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $parent = MediaCategory::factory()->create();
        MediaCategory::factory()->count(3)->child($parent)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['total_categories'] === 1);
    }
}
