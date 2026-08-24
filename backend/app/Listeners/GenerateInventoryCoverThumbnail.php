<?php

namespace App\Listeners;

use App\Events\MediaInventoryCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateInventoryCoverThumbnail implements ShouldQueue
{
    public const THUMBNAIL_WIDTH = 400;

    public const THUMBNAIL_HEIGHT = 400;

    /**
     * Handle the event.
     */
    public function handle(MediaInventoryCreated $event): void
    {
        $cover = $event->inventory->images()->where('is_cover', true)->first();

        if (! $cover || ! Storage::disk('public')->exists($cover->path)) {
            return;
        }

        $thumbnailPath = $this->thumbnailPath($cover->path);

        try {
            $this->generate($cover->path, $thumbnailPath);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate inventory cover thumbnail', [
                'inventory_id' => $event->inventory->id,
                'image_path' => $cover->path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function thumbnailPath(string $originalPath): string
    {
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $withoutExtension = Str::beforeLast($originalPath, ".{$extension}");

        return "{$withoutExtension}-thumb.jpg";
    }

    private function generate(string $sourcePath, string $thumbnailPath): void
    {
        $sourceContents = Storage::disk('public')->get($sourcePath);
        $sourceImage = imagecreatefromstring($sourceContents);

        if (! $sourceImage) {
            throw new \RuntimeException("Unable to read image data from [{$sourcePath}].");
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $ratio = max(self::THUMBNAIL_WIDTH / $sourceWidth, self::THUMBNAIL_HEIGHT / $sourceHeight);
        $resizedWidth = (int) round($sourceWidth * $ratio);
        $resizedHeight = (int) round($sourceHeight * $ratio);

        $resized = imagescale($sourceImage, $resizedWidth, $resizedHeight);

        $cropX = (int) (($resizedWidth - self::THUMBNAIL_WIDTH) / 2);
        $cropY = (int) (($resizedHeight - self::THUMBNAIL_HEIGHT) / 2);

        $thumbnail = imagecreatetruecolor(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        imagecopy($thumbnail, $resized, 0, 0, $cropX, $cropY, self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);

        ob_start();
        imagejpeg($thumbnail, null, 85);
        $thumbnailContents = ob_get_clean();

        Storage::disk('public')->put($thumbnailPath, $thumbnailContents);

        imagedestroy($sourceImage);
        imagedestroy($resized);
        imagedestroy($thumbnail);
    }
}
