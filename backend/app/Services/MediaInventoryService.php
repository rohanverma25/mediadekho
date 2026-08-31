<?php

namespace App\Services;

use App\Events\MediaInventoryCreated;
use App\Events\MediaInventoryPriceUpdated;
use App\Helpers\ImageUploadHelper;
use App\Models\MediaInventory;
use App\Models\MediaInventoryPrice;
use App\Repositories\Contracts\MediaInventoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MediaInventoryService
{
    private const IMAGE_DIRECTORY = 'media-inventory';

    private const META_IMAGE_DIRECTORY = 'media-inventory-meta';

    private const DOCUMENT_DIRECTORY = 'media-inventory-documents';

    public function __construct(
        private readonly MediaInventoryRepositoryInterface $repository,
    ) {
    }

    /**
     * @param  UploadedFile[]  $gallery
     * @param  UploadedFile[]  $documents
     * @param  array<int, array{label: string, value: string}>  $keyInsights
     */
    public function create(array $data, ?UploadedFile $image, array $gallery = [], array $documents = [], array $keyInsights = [], ?UploadedFile $metaImage = null): MediaInventory
    {
        if ($image) {
            $data['image'] = ImageUploadHelper::upload($image, self::IMAGE_DIRECTORY);
        }

        if ($metaImage) {
            $data['meta_image'] = ImageUploadHelper::upload($metaImage, self::META_IMAGE_DIRECTORY);
        }

        $inventory = DB::transaction(function () use ($data, $gallery, $documents, $keyInsights) {
            $inventory = $this->repository->create($data);

            $this->attachGallery($inventory, $gallery);
            $this->attachDocuments($inventory, $documents);
            $this->syncKeyInsights($inventory, $keyInsights);

            return $inventory;
        });

        MediaInventoryCreated::dispatch($inventory->fresh(['images', 'files']));

        return $inventory;
    }

    /**
     * @param  UploadedFile[]  $gallery
     * @param  UploadedFile[]  $documents
     * @param  array<int, array{label: string, value: string}>  $keyInsights
     */
    public function update(MediaInventory $inventory, array $data, ?UploadedFile $image, array $gallery = [], array $documents = [], array $keyInsights = [], ?UploadedFile $metaImage = null): MediaInventory
    {
        if ($image) {
            ImageUploadHelper::delete($inventory->image);
            $data['image'] = ImageUploadHelper::upload($image, self::IMAGE_DIRECTORY);
        }

        if ($metaImage) {
            ImageUploadHelper::delete($inventory->meta_image);
            $data['meta_image'] = ImageUploadHelper::upload($metaImage, self::META_IMAGE_DIRECTORY);
        }

        return DB::transaction(function () use ($inventory, $data, $gallery, $documents, $keyInsights) {
            $inventory = $this->repository->update($inventory, $data);

            $this->attachGallery($inventory, $gallery);
            $this->attachDocuments($inventory, $documents);
            $this->syncKeyInsights($inventory, $keyInsights);

            return $inventory;
        });
    }

    /**
     * Full replace-on-save: the admin form resubmits the complete list of
     * insight rows on every save (rows can be freely added/removed client
     * side), so the simplest correct sync is to drop and recreate rather
     * than diff against what's already stored.
     *
     * @param  array<int, array{label: string, value: string, show_after_heading?: mixed}>  $keyInsights
     */
    private function syncKeyInsights(MediaInventory $inventory, array $keyInsights): void
    {
        if (empty($keyInsights)) {
            return;
        }

        $inventory->keyInsights()->delete();

        foreach (array_values($keyInsights) as $index => $row) {
            if (blank($row['label'] ?? null) || blank($row['value'] ?? null)) {
                continue;
            }

            $inventory->keyInsights()->create([
                'label' => $row['label'],
                'value' => $row['value'],
                // Checkbox inputs are simply absent from the payload when
                // unchecked (the admin form's hidden-input-plus-checkbox
                // trick sends a literal "0"/"1" either way), so this always
                // resolves to an explicit boolean rather than leaving it
                // ambiguous.
                'show_after_heading' => filter_var($row['show_after_heading'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'sort_order' => $index,
            ]);
        }
    }

    public function delete(MediaInventory $inventory): bool
    {
        return $this->repository->delete($inventory);
    }

    /**
     * Retail/B2C/B2B are never entered directly — the admin sets a markup
     * percentage over base_price for each tier, and the actual tier price is
     * derived here: tier_price = base_price + (base_price * percentage / 100).
     * Enterprise stays a direct, custom-negotiated price (no percentage).
     */
    public function setPrice(MediaInventory $inventory, array $data): MediaInventoryPrice
    {
        $basePrice = (float) $data['base_price'];

        foreach (['retail' => 'retail_percentage', 'b2c' => 'b2c_percentage', 'b2b' => 'b2b_percentage'] as $tier => $percentageField) {
            $percentage = (float) ($data[$percentageField] ?? 0);
            $data["{$tier}_price"] = round($basePrice + ($basePrice * $percentage / 100), 2);
        }

        $price = DB::transaction(fn () => $inventory->price()->updateOrCreate(['inventory_id' => $inventory->id], $data));

        MediaInventoryPriceUpdated::dispatch($price->fresh());

        return $price;
    }

    /**
     * @param  UploadedFile[]  $gallery
     */
    private function attachGallery(MediaInventory $inventory, array $gallery): void
    {
        if (empty($gallery)) {
            return;
        }

        $hasCover = $inventory->images()->where('is_cover', true)->exists();

        foreach ($gallery as $index => $file) {
            $inventory->images()->create([
                'path' => ImageUploadHelper::upload($file, self::IMAGE_DIRECTORY),
                'is_cover' => ! $hasCover && $index === 0,
                'sort_order' => $inventory->images()->count(),
            ]);
        }
    }

    /**
     * @param  UploadedFile[]  $documents
     */
    private function attachDocuments(MediaInventory $inventory, array $documents): void
    {
        foreach ($documents as $file) {
            $inventory->files()->create([
                'path' => ImageUploadHelper::upload($file, self::DOCUMENT_DIRECTORY),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
