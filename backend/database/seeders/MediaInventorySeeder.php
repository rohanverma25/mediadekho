<?php

namespace Database\Seeders;

use App\Models\Frequency;
use App\Models\Language;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use App\Models\MediaInventoryAvailability;
use App\Models\MediaInventoryFile;
use App\Models\MediaInventoryImage;
use App\Models\MediaInventoryPrice;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaInventorySeeder extends Seeder
{
    /**
     * Number of inventory records to seed. Override with the
     * MEDIA_INVENTORY_SEED_COUNT env var for larger runs (e.g. load testing
     * at 100k+ rows) — the default of 200 is sized for local development.
     */
    private const DEFAULT_COUNT = 200;

    private const TOP_LEVEL_CATEGORIES = 8;

    private const SUBCATEGORIES_PER_CATEGORY = 3;

    private const FREQUENCY_COUNT = 6;

    private const LANGUAGE_COUNT = 6;

    public function run(): void
    {
        $count = (int) (env('MEDIA_INVENTORY_SEED_COUNT') ?: self::DEFAULT_COUNT);

        $creator = User::query()->where('email', 'dheer725@gmail.com')->first()
            ?? User::factory()->create();

        // Pooled once, then assigned by id below — letting each of the
        // (potentially hundreds of) inventory rows spawn its own random
        // Frequency/Language via the nested factory default would exhaust
        // Faker's unique() pool and collide against the unique `name` column.
        $frequencies = Frequency::factory()->count(self::FREQUENCY_COUNT)->create();
        $languages = Language::factory()->count(self::LANGUAGE_COUNT)->create();

        $subcategories = collect();

        MediaCategory::factory()
            ->count(self::TOP_LEVEL_CATEGORIES)
            ->create()
            ->each(function (MediaCategory $parent) use ($subcategories) {
                MediaCategory::factory()
                    ->count(self::SUBCATEGORIES_PER_CATEGORY)
                    ->child($parent)
                    ->create()
                    ->each(fn (MediaCategory $child) => $subcategories->push($child));
            });

        for ($i = 0; $i < $count; $i++) {
            $subcategory = $subcategories->random();

            $inventory = MediaInventory::factory()->create([
                'category_id' => $subcategory->parent_id,
                'subcategory_id' => $subcategory->id,
                'frequency_id' => $frequencies->random()->id,
                'language_id' => $languages->random()->id,
                'created_by' => $creator->id,
            ]);

            MediaInventoryPrice::factory()->create(['inventory_id' => $inventory->id]);

            MediaInventoryImage::factory()->cover()->create(['inventory_id' => $inventory->id]);
            MediaInventoryImage::factory()->count(random_int(0, 3))->create(['inventory_id' => $inventory->id]);

            MediaInventoryFile::factory()->count(random_int(0, 2))->create(['inventory_id' => $inventory->id]);

            // Distinct days, picked without replacement — the factory's own
            // random date can collide within the same inventory_id (the
            // table has a unique(inventory_id, date) constraint), so the
            // uniqueness has to be guaranteed here rather than in the factory.
            collect(range(0, 89))
                ->shuffle()
                ->take(random_int(1, 5))
                ->each(fn (int $offset) => MediaInventoryAvailability::factory()->create([
                    'inventory_id' => $inventory->id,
                    'date' => now()->addDays($offset)->startOfDay(),
                ]));
        }

        $this->command?->info("Seeded {$count} media inventory records across ".$subcategories->count().' subcategories.');
    }
}
