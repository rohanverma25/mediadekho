<?php

namespace Tests\Feature\Admin;

use App\Models\Magazine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRolesAndPermissions;

class MagazineControllerTest extends TestCase
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
        $this->get(route('admin.magazines.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_role_cannot_access_magazines_admin(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->get(route('admin.magazines.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_a_magazine_with_a_pdf(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->post(route('admin.magazines.store'), [
                'title' => 'August 2026 Issue',
                'description' => 'Our biggest issue yet.',
                'pdf_file' => UploadedFile::fake()->create('august.pdf', 5000, 'application/pdf'),
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
                'status' => 'active',
                'sort_order' => 0,
            ])
            ->assertCreated();

        $magazine = Magazine::query()->where('title', 'August 2026 Issue')->firstOrFail();

        $this->assertSame('august-2026-issue', $magazine->slug);
        Storage::disk('public')->assertExists($magazine->pdf_file);
        Storage::disk('public')->assertExists($magazine->cover_image);
    }

    public function test_creating_a_magazine_without_a_pdf_fails_validation(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.magazines.store'), [
                'title' => 'Missing PDF',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('pdf_file');
    }

    public function test_non_pdf_file_is_rejected(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.magazines.store'), [
                'title' => 'Bad File Type',
                'pdf_file' => UploadedFile::fake()->image('not-a-pdf.jpg'),
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('pdf_file');
    }

    public function test_super_admin_can_update_a_magazine_without_replacing_the_pdf(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $magazine = Magazine::factory()->create(['title' => 'Original Title', 'pdf_file' => 'magazines/original.pdf']);
        Storage::disk('public')->put('magazines/original.pdf', 'fake-pdf-content');

        $this->actingAs($admin)
            ->putJson(route('admin.magazines.update', $magazine), [
                'title' => 'Renamed Issue',
                'status' => 'inactive',
            ])
            ->assertOk();

        $magazine->refresh();
        $this->assertSame('Renamed Issue', $magazine->title);
        $this->assertSame('magazines/original.pdf', $magazine->pdf_file);
        Storage::disk('public')->assertExists('magazines/original.pdf');
    }

    public function test_replacing_the_pdf_deletes_the_old_one(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $magazine = Magazine::factory()->create(['pdf_file' => 'magazines/original.pdf']);
        Storage::disk('public')->put('magazines/original.pdf', 'fake-pdf-content');

        $this->actingAs($admin)->post(route('admin.magazines.update', $magazine), [
            '_method' => 'PUT',
            'title' => $magazine->title,
            'status' => 'active',
            'pdf_file' => UploadedFile::fake()->create('new.pdf', 3000, 'application/pdf'),
        ])->assertOk();

        $magazine->refresh();
        Storage::disk('public')->assertMissing('magazines/original.pdf');
        Storage::disk('public')->assertExists($magazine->pdf_file);
    }

    public function test_super_admin_can_delete_a_magazine(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $magazine = Magazine::factory()->create(['pdf_file' => 'magazines/to-delete.pdf']);
        Storage::disk('public')->put('magazines/to-delete.pdf', 'fake-pdf-content');

        $this->actingAs($admin)
            ->deleteJson(route('admin.magazines.destroy', $magazine))
            ->assertOk();

        $this->assertDatabaseMissing('magazines', ['id' => $magazine->id]);
        Storage::disk('public')->assertMissing('magazines/to-delete.pdf');
    }

    public function test_customer_role_cannot_create_a_magazine(): void
    {
        $customer = $this->userWithRole('B2B Customer');

        $this->actingAs($customer)
            ->postJson(route('admin.magazines.store'), [
                'title' => 'Should Not Work',
                'status' => 'active',
            ])
            ->assertForbidden();
    }
}
