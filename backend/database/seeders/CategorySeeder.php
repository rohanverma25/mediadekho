<?php

namespace Database\Seeders;

use App\Helpers\SlugHelper;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Verticals shown in the storefront's category navigation (CategorySlider,
     * home page quick links) — kept in sync with react-app/src/data/mediaData.js
     * CATEGORIES_LIST so the live API mirrors what the frontend expects.
     */
    private const CATEGORIES = [
        ['name' => 'Magazine Advertising', 'description' => 'Full-page, cover, and spread placements across premium national and trade magazines.'],
        ['name' => 'Airport Advertising', 'description' => 'Digital screens, lounges, and terminal branding across major domestic and international airports.'],
        ['name' => 'Transit & Metro', 'description' => 'Metro train wraps, station branding, and bus shelter media across high-footfall transit routes.'],
        ['name' => 'Outdoor & Billboards', 'description' => 'Hoardings, unipoles, and large-format outdoor sites in high-visibility city locations.'],
        ['name' => 'Cinema Advertising', 'description' => 'On-screen commercial spots and lobby branding across leading multiplex chains.'],
        ['name' => 'App Takeover', 'description' => 'Full-screen interstitial and banner takeovers on high-traffic consumer apps.'],
        ['name' => 'Influencer Marketing', 'description' => 'Sponsored content and collaborations with vetted social media creators.'],
        ['name' => 'OTT & Streaming', 'description' => 'Pre-roll and mid-roll video ad placements across streaming platforms.'],
        ['name' => 'Stadium Branding', 'description' => 'LED perimeter boards and stadium signage during live sporting events.'],
        ['name' => 'Team Sponsorship', 'description' => 'Jersey, kit, and team sponsorship placements across sports franchises.'],
        ['name' => 'Corporate Merchandise', 'description' => 'Branded corporate gifting and merchandise for events and campaigns.'],
        ['name' => 'Festival Swag', 'description' => 'On-ground branding and giveaways at music and cultural festivals.'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::query()->firstOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'slug' => SlugHelper::unique(Category::class, $category['name']),
                ],
            );
        }

        $this->command?->info('Seeded '.count(self::CATEGORIES).' categories.');
    }
}
