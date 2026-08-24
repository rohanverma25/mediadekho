<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class NewsControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.news.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_news_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.news.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_news_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.news.index'))
            ->assertOk();
    }

    public function test_super_admin_can_create_news_item(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.news.store'), [
                'title' => 'Media Dekho Raises Series A Funding',
                'link' => 'https://example.com/media-dekho-funding',
                'image' => UploadedFile::fake()->image('screenshot.png'),
                'status' => 'active',
                'sort_order' => 1,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('news', [
            'title' => 'Media Dekho Raises Series A Funding',
            'link' => 'https://example.com/media-dekho-funding',
        ]);

        $news = News::query()->where('title', 'Media Dekho Raises Series A Funding')->firstOrFail();
        Storage::disk('public')->assertExists($news->image);
    }

    public function test_creating_news_item_without_an_image_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.news.store'), [
                'title' => 'Missing Image',
                'link' => 'https://example.com/article',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_creating_news_item_without_a_link_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.news.store'), [
                'title' => 'Missing Link',
                'image' => UploadedFile::fake()->image('screenshot.png'),
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('link');
    }

    public function test_customer_role_cannot_create_news_item(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.news.store'), [
                'title' => 'Should Not Work',
                'link' => 'https://example.com/article',
                'image' => UploadedFile::fake()->image('screenshot.png'),
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_news_item_without_replacing_the_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $news = News::factory()->create(['title' => 'Original Title', 'image' => 'news/original.png']);

        $this->actingAs($admin)
            ->putJson(route('admin.news.update', $news), [
                'title' => 'Renamed Headline',
                'link' => $news->link,
                'status' => 'inactive',
                'sort_order' => 5,
            ])
            ->assertOk();

        $this->assertDatabaseHas('news', [
            'id' => $news->id,
            'title' => 'Renamed Headline',
            'status' => 'inactive',
            'image' => 'news/original.png',
        ]);
    }

    public function test_super_admin_can_replace_news_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $news = News::factory()->create(['image' => 'news/original.png']);
        Storage::disk('public')->put('news/original.png', 'fake-contents');

        $this->actingAs($admin)
            ->putJson(route('admin.news.update', $news), [
                'title' => $news->title,
                'link' => $news->link,
                'status' => 'active',
                'image' => UploadedFile::fake()->image('new-screenshot.png'),
            ])
            ->assertOk();

        $news->refresh();
        Storage::disk('public')->assertMissing('news/original.png');
        Storage::disk('public')->assertExists($news->image);
    }

    public function test_super_admin_can_delete_news_item(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $news = News::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.news.destroy', $news))
            ->assertOk();

        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }
}
