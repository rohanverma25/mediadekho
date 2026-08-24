<?php

namespace Database\Factories;

use App\Models\MediaInventory;
use App\Models\MediaInventoryFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaInventoryFile>
 */
class MediaInventoryFileFactory extends Factory
{
    private const MIME_TYPES = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = $this->faker->randomElement(array_keys(self::MIME_TYPES));

        return [
            'inventory_id' => MediaInventory::factory(),
            'path' => 'media-inventory-documents/'.Str::uuid().'.'.$extension,
            'original_name' => $this->faker->words(2, true).'.'.$extension,
            'mime_type' => self::MIME_TYPES[$extension],
            'size' => $this->faker->numberBetween(50_000, 5_000_000),
        ];
    }
}
