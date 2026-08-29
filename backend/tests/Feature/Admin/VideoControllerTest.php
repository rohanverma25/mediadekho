<?php

namespace Tests\Feature\Admin;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class VideoControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        Storage::fake('public');
    }

    public function test_super_admin_can_create_a_youtube_video(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.videos.store'), [
                'title' => 'How Media Buying Works',
                'source_type' => 'youtube',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'status' => 'active',
                'sort_order' => 0,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('videos', [
            'title' => 'How Media Buying Works',
            'source_type' => 'youtube',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_path' => null,
        ]);
    }

    public function test_creating_a_youtube_video_without_a_valid_url_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.videos.store'), [
                'title' => 'Bad Link',
                'source_type' => 'youtube',
                'youtube_url' => 'https://example.com/not-a-video',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('youtube_url');
    }

    public function test_super_admin_can_upload_a_video_file(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->post(route('admin.videos.store'), [
                'title' => 'Client Testimonial',
                'source_type' => 'upload',
                'video_file' => UploadedFile::fake()->create('testimonial.mp4', 5000, 'video/mp4'),
                'status' => 'active',
                'sort_order' => 0,
            ])
            ->assertCreated();

        $video = Video::query()->where('title', 'Client Testimonial')->firstOrFail();

        $this->assertSame('upload', $video->source_type);
        $this->assertSame('', $video->youtube_url);
        Storage::disk('public')->assertExists($video->video_path);
    }

    public function test_super_admin_can_upload_a_video_with_a_custom_thumbnail(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->post(route('admin.videos.store'), [
                'title' => 'Client Testimonial',
                'source_type' => 'upload',
                'video_file' => UploadedFile::fake()->create('testimonial.mp4', 5000, 'video/mp4'),
                'thumbnail_file' => UploadedFile::fake()->image('poster.jpg'),
                'status' => 'active',
                'sort_order' => 0,
            ])
            ->assertCreated();

        $video = Video::query()->where('title', 'Client Testimonial')->firstOrFail();
        Storage::disk('public')->assertExists($video->thumbnail_path);
        $this->assertNotNull($video->thumbnail_url);
    }

    public function test_uploading_a_video_without_a_file_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.videos.store'), [
                'title' => 'Missing File',
                'source_type' => 'upload',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('video_file');
    }

    public function test_super_admin_can_replace_an_uploaded_video_file(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $video = Video::factory()->create([
            'source_type' => 'upload',
            'youtube_url' => '',
            'video_path' => 'videos/original.mp4',
        ]);
        Storage::disk('public')->put('videos/original.mp4', 'fake-content');

        $this->actingAs($admin)
            ->post(route('admin.videos.update', $video), [
                '_method' => 'PUT',
                'title' => $video->title,
                'source_type' => 'upload',
                'video_file' => UploadedFile::fake()->create('new.mp4', 3000, 'video/mp4'),
                'status' => 'active',
                'sort_order' => 0,
            ])
            ->assertOk();

        $video->refresh();
        Storage::disk('public')->assertMissing('videos/original.mp4');
        Storage::disk('public')->assertExists($video->video_path);
    }

    public function test_updating_an_uploaded_video_without_a_new_file_keeps_the_existing_one(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $video = Video::factory()->create([
            'source_type' => 'upload',
            'youtube_url' => '',
            'video_path' => 'videos/original.mp4',
        ]);
        Storage::disk('public')->put('videos/original.mp4', 'fake-content');

        $this->actingAs($admin)
            ->putJson(route('admin.videos.update', $video), [
                'title' => 'Renamed',
                'source_type' => 'upload',
                'status' => 'active',
                'sort_order' => 0,
            ])
            ->assertOk();

        $video->refresh();
        $this->assertSame('videos/original.mp4', $video->video_path);
        Storage::disk('public')->assertExists('videos/original.mp4');
    }

    public function test_switching_an_uploaded_video_to_youtube_deletes_the_stored_file(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $video = Video::factory()->create([
            'source_type' => 'upload',
            'youtube_url' => '',
            'video_path' => 'videos/original.mp4',
        ]);
        Storage::disk('public')->put('videos/original.mp4', 'fake-content');

        $this->actingAs($admin)
            ->putJson(route('admin.videos.update', $video), [
                'title' => $video->title,
                'source_type' => 'youtube',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'status' => 'active',
                'sort_order' => 0,
            ])
            ->assertOk();

        $video->refresh();
        $this->assertSame('youtube', $video->source_type);
        $this->assertNull($video->video_path);
        Storage::disk('public')->assertMissing('videos/original.mp4');
    }

    public function test_deleting_a_video_removes_its_stored_files(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $video = Video::factory()->create([
            'source_type' => 'upload',
            'youtube_url' => '',
            'video_path' => 'videos/original.mp4',
            'thumbnail_path' => 'video-thumbnails/poster.jpg',
        ]);
        Storage::disk('public')->put('videos/original.mp4', 'fake-content');
        Storage::disk('public')->put('video-thumbnails/poster.jpg', 'fake-content');

        $this->actingAs($admin)
            ->deleteJson(route('admin.videos.destroy', $video))
            ->assertOk();

        $this->assertDatabaseMissing('videos', ['id' => $video->id]);
        Storage::disk('public')->assertMissing('videos/original.mp4');
        Storage::disk('public')->assertMissing('video-thumbnails/poster.jpg');
    }

    public function test_customer_role_cannot_create_a_video(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.videos.store'), [
                'title' => 'Should Not Work',
                'source_type' => 'youtube',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'status' => 'active',
            ])
            ->assertForbidden();
    }
}
