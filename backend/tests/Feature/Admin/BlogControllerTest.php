<?php

namespace Tests\Feature\Admin;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class BlogControllerTest extends TestCase
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
        $this->get(route('admin.blogs.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_blog_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.blogs.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_blog_index(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.blogs.index'))
            ->assertOk();
    }

    public function test_super_admin_can_create_blog_post_and_slug_is_auto_generated(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.blogs.store'), [
                'title' => 'How Outdoor Advertising Still Wins Attention',
                'excerpt' => 'A look at why billboards still work in a digital-first world.',
                'content' => '<p>Full article content goes here.</p>',
                'author_name' => 'Media Dekho Team',
                'featured_image' => UploadedFile::fake()->image('cover.jpg'),
                'status' => 'published',
                'published_at' => '2026-08-20',
            ])
            ->assertCreated();

        $blog = Blog::query()->where('title', 'How Outdoor Advertising Still Wins Attention')->firstOrFail();
        $this->assertSame('how-outdoor-advertising-still-wins-attention', $blog->slug);
        Storage::disk('public')->assertExists($blog->featured_image);
    }

    public function test_creating_blog_post_without_content_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.blogs.store'), [
                'title' => 'Missing Content',
                'status' => 'draft',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('content');
    }

    public function test_customer_role_cannot_create_blog_post(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.blogs.store'), [
                'title' => 'Should Not Work',
                'content' => '<p>Nope.</p>',
                'status' => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_blog_post_without_replacing_the_image(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $blog = Blog::factory()->create(['title' => 'Original Title', 'featured_image' => 'blogs/original.jpg']);

        $this->actingAs($admin)
            ->putJson(route('admin.blogs.update', $blog), [
                'title' => 'Original Title',
                'content' => $blog->content,
                'status' => 'draft',
            ])
            ->assertOk();

        $this->assertDatabaseHas('blogs', [
            'id' => $blog->id,
            'status' => 'draft',
            'featured_image' => 'blogs/original.jpg',
        ]);
    }

    public function test_updating_title_regenerates_the_slug(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $blog = Blog::factory()->create(['title' => 'Old Title']);

        $this->actingAs($admin)
            ->putJson(route('admin.blogs.update', $blog), [
                'title' => 'Brand New Title',
                'content' => $blog->content,
                'status' => $blog->status,
            ])
            ->assertOk();

        $this->assertSame('brand-new-title', $blog->fresh()->slug);
    }

    public function test_super_admin_can_delete_blog_post(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $blog = Blog::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('admin.blogs.destroy', $blog))
            ->assertOk();

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }
}
