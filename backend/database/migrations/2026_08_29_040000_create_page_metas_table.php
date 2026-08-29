<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The fixed set of static, indexable pages an admin can override SEO
     * meta tags for — dynamic pages (categories, listings, blog posts) get
     * their own meta_title/meta_description/meta_image fields on their own
     * model instead, since there can be thousands of them.
     */
    private const PAGES = [
        'home' => 'Homepage',
        'contact' => 'Contact Us',
        'faq' => 'FAQ Page',
        'blogs' => 'Blog Listing',
        'news' => 'News Page',
        'awards' => 'Awards Page',
        'career' => 'Careers Page',
        'clients' => 'Clients Page',
    ];

    public function up(): void
    {
        Schema::create('page_metas', function (Blueprint $table) {
            $table->id();
            // Fixed, seeded set of static pages (home, contact, faq, ...) —
            // not admin-creatable, so this is the stable key the frontend
            // requests by, independent of the admin-editable `title`.
            $table->string('page_key')->unique();
            $table->string('label');
            $table->string('title')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        // Seeded here (not a demo-content seeder) so every static page has
        // a row to edit immediately after this migrates — no separate
        // seeder command to remember to run in production.
        foreach (self::PAGES as $key => $label) {
            DB::table('page_metas')->insert([
                'page_key' => $key,
                'label' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('page_metas');
    }
};
