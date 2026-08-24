<?php

namespace Tests\Feature;

use App\Models\MediaCategory;
use App\Models\MediaInventory;
use App\Models\User;
use App\Services\MediaInventoryImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MediaInventoryImportExportTest extends TestCase
{
    use RefreshDatabase;

    private MediaInventoryImportExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MediaInventoryImportExportService::class);
    }

    private function csvFile(string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'import_test_').'.csv';
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'import.csv', 'text/csv', null, true);
    }

    public function test_import_creates_new_inventory_and_auto_creates_category(): void
    {
        $creator = User::factory()->create();
        $csv = "title,category,status\n"
            ."New Media Title,Brand New Category,draft\n";

        $result = $this->service->importCsv($this->csvFile($csv), $creator);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertEmpty($result['failed']);

        $this->assertDatabaseHas('media_inventory', ['title' => 'New Media Title']);
        $this->assertDatabaseHas('media_categories', ['name' => 'Brand New Category']);
    }

    public function test_import_updates_existing_inventory_matched_by_title(): void
    {
        $creator = User::factory()->create();
        $category = MediaCategory::factory()->create(['name' => 'Existing Category']);
        $existing = MediaInventory::factory()->create([
            'category_id' => $category->id,
            'title' => 'Repeat Title',
            'short_description' => 'Old Description',
        ]);

        $csv = "title,category,status,short_description\n"
            ."Repeat Title,Existing Category,published,New Description\n";

        $result = $this->service->importCsv($this->csvFile($csv), $creator);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['updated']);

        $existing->refresh();
        $this->assertSame('New Description', $existing->short_description);
        $this->assertSame('published', $existing->status);
    }

    /**
     * Regression test: an earlier version used `?:` instead of `??` when
     * reading the optional enterprise_price column, which threw an
     * "undefined array key" warning whenever a CSV omitted that column.
     */
    public function test_import_succeeds_without_optional_enterprise_price_column(): void
    {
        $creator = User::factory()->create();
        $csv = "title,category,status,base_price,retail_price,b2c_price,b2b_price\n"
            ."No Enterprise Col,Category,draft,500,750,700,600\n";

        $result = $this->service->importCsv($this->csvFile($csv), $creator);

        $this->assertSame(1, $result['created']);
        $this->assertEmpty($result['failed']);

        $inventory = MediaInventory::where('title', 'No Enterprise Col')->first();
        $this->assertNotNull($inventory->price);
        $this->assertNull($inventory->price->enterprise_price);
        $this->assertEquals(500, $inventory->price->base_price);
    }

    public function test_import_sets_pricing_when_price_columns_present(): void
    {
        $creator = User::factory()->create();
        $csv = "title,category,status,base_price,retail_price,b2c_price,b2b_price,enterprise_price\n"
            ."Priced Item,Category,draft,1000,1500,1400,1200,1050\n";

        $this->service->importCsv($this->csvFile($csv), $creator);

        $inventory = MediaInventory::where('title', 'Priced Item')->first();
        $this->assertEquals(1000, $inventory->price->base_price);
        $this->assertEquals(1050, $inventory->price->enterprise_price);
    }

    public function test_import_reports_failed_row_when_required_value_missing(): void
    {
        $creator = User::factory()->create();
        $csv = "title,category,status\n"
            .",Category,draft\n";

        $result = $this->service->importCsv($this->csvFile($csv), $creator);

        $this->assertSame(0, $result['created']);
        $this->assertCount(1, $result['failed']);
        $this->assertSame(2, $result['failed'][0]['row']);
    }

    public function test_import_throws_when_required_header_columns_missing(): void
    {
        $creator = User::factory()->create();
        $csv = "title,status\nSome Title,draft\n";

        $this->expectException(\InvalidArgumentException::class);

        $this->service->importCsv($this->csvFile($csv), $creator);
    }

    public function test_export_streams_csv_with_expected_header_and_data(): void
    {
        $category = MediaCategory::factory()->create(['name' => 'Export Category']);
        MediaInventory::factory()->create([
            'category_id' => $category->id,
            'title' => 'Export Test Title',
        ]);

        $response = $this->service->exportCsv();
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('short_description', $content);
        $this->assertStringContainsString('Export Test Title', $content);
        $this->assertStringContainsString('Export Category', $content);
    }
}
