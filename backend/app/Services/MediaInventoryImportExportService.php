<?php

namespace App\Services;

use App\Models\Frequency;
use App\Models\Language;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaInventoryImportExportService
{
    private const EXPORT_COLUMNS = [
        'id', 'title', 'slug', 'short_description', 'category', 'subcategory', 'frequency',
        'language', 'status', 'base_price', 'retail_price', 'b2c_price', 'b2b_price',
        'enterprise_price', 'created_by', 'created_at',
    ];

    /**
     * Columns a CSV must provide to be importable. Everything else is optional.
     */
    private const REQUIRED_IMPORT_COLUMNS = ['title', 'category'];

    /**
     * Streams the export row-by-row via a DB cursor so memory usage stays
     * flat regardless of dataset size — this is the piece that has to hold
     * up at 100k+ rows, so nothing is ever materialized into a PHP array.
     */
    public function exportCsv(array $filters = []): StreamedResponse
    {
        $query = MediaInventory::query()->with(['category', 'subcategory', 'frequency', 'language', 'price', 'creator']);

        $query->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['frequency_id'] ?? null, fn ($q, $v) => $q->where('frequency_id', $v))
            ->when($filters['language_id'] ?? null, fn ($q, $v) => $q->where('language_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v));

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::EXPORT_COLUMNS);

            foreach ($query->cursor() as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->title,
                    $item->slug,
                    $item->short_description,
                    $item->category?->name,
                    $item->subcategory?->name,
                    $item->frequency?->name,
                    $item->language?->name,
                    $item->status,
                    $item->price?->base_price,
                    $item->price?->retail_price,
                    $item->price?->b2c_price,
                    $item->price?->b2b_price,
                    $item->price?->enterprise_price,
                    $item->creator?->name,
                    $item->created_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="media-inventory-'.now()->format('Y-m-d-His').'.csv"',
        ]);
    }

    /**
     * Chunked, upsert-by-title import. Matched rows update in place;
     * unmatched rows create new inventory. Category, frequency, and language
     * are all matched by name, auto-creating them if they don't exist yet —
     * so a CSV can introduce new lookups without a separate import step.
     *
     * @return array{created: int, updated: int, failed: array<int, array{row: int, reason: string}>}
     */
    public function importCsv(UploadedFile $file, User $creator): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $missing = array_diff(self::REQUIRED_IMPORT_COLUMNS, $header);
        if ($missing) {
            fclose($handle);

            throw new \InvalidArgumentException('CSV is missing required column(s): '.implode(', ', $missing));
        }

        $created = 0;
        $updated = 0;
        $failed = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($row) !== count($header)) {
                $failed[] = ['row' => $rowNumber, 'reason' => 'Column count does not match header.'];

                continue;
            }

            $record = array_combine($header, $row);

            try {
                DB::transaction(function () use ($record, $creator, &$created, &$updated) {
                    $this->upsertRow($record, $creator, $created, $updated);
                });
            } catch (\Throwable $e) {
                $failed[] = ['row' => $rowNumber, 'reason' => $e->getMessage()];
            }
        }

        fclose($handle);

        return ['created' => $created, 'updated' => $updated, 'failed' => $failed];
    }

    private function upsertRow(array $record, User $creator, int &$created, int &$updated): void
    {
        foreach (self::REQUIRED_IMPORT_COLUMNS as $column) {
            if (blank($record[$column] ?? null)) {
                throw new \InvalidArgumentException("Missing required value for '{$column}'.");
            }
        }

        $category = MediaCategory::query()->firstOrCreate(
            ['name' => $record['category']],
            ['status' => 'active'],
        );

        $subcategoryId = null;
        if (! empty($record['subcategory'])) {
            $subcategoryId = MediaCategory::query()->firstOrCreate(
                ['name' => $record['subcategory'], 'parent_id' => $category->id],
                ['status' => 'active'],
            )->id;
        }

        $frequencyId = null;
        if (! empty($record['frequency'])) {
            $frequencyId = Frequency::query()->firstOrCreate(['name' => $record['frequency']])->id;
        }

        $languageId = null;
        if (! empty($record['language'])) {
            $languageId = Language::query()->firstOrCreate(['name' => $record['language']])->id;
        }

        $inventory = MediaInventory::query()->where('title', $record['title'])->first();
        $isNew = ! $inventory;

        $attributes = [
            'category_id' => $category->id,
            'subcategory_id' => $subcategoryId,
            'frequency_id' => $frequencyId,
            'language_id' => $languageId,
            'title' => $record['title'],
            'short_description' => $record['short_description'] ?? null,
            'status' => $record['status'] ?? 'draft',
        ];

        if ($isNew) {
            $attributes['created_by'] = $creator->id;
            $inventory = MediaInventory::query()->create($attributes);
            $created++;
        } else {
            $inventory->update($attributes);
            $updated++;
        }

        if (isset($record['base_price']) && is_numeric($record['base_price'])) {
            $inventory->price()->updateOrCreate(['inventory_id' => $inventory->id], [
                'base_price' => $record['base_price'],
                'retail_price' => $record['retail_price'] ?? $record['base_price'],
                'b2c_price' => $record['b2c_price'] ?? $record['base_price'],
                'b2b_price' => $record['b2b_price'] ?? $record['base_price'],
                'enterprise_price' => empty($record['enterprise_price'] ?? null) ? null : $record['enterprise_price'],
            ]);
        }
    }
}
