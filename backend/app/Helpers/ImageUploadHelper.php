<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadHelper
{
    /**
     * Store an uploaded image on the public disk and return its relative path.
     */
    public static function upload(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Download an image from a URL and store it on the public disk. Returns null on failure.
     */
    public static function uploadFromUrl(string $url, string $directory): ?string
    {
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful() || ! Str::startsWith($response->header('Content-Type'), 'image/')) {
            return null;
        }

        $extension = str($response->header('Content-Type'))->after('image/')->before(';')->toString() ?: 'jpg';
        $path = $directory.'/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($path, $response->body());

        return $path;
    }

    /**
     * Delete a previously stored image from the public disk, if it exists.
     */
    public static function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
