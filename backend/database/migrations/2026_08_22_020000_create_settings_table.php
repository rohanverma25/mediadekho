<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('contact_address')->nullable();
            $table->text('footer_description')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->text('header_scripts')->nullable();
            $table->text('footer_scripts')->nullable();
            $table->timestamps();
        });

        // Singleton config row — seeded here (not a demo-content seeder)
        // with the values already hardcoded in Header.jsx/Footer.jsx, so
        // the site's appearance doesn't change the moment this migrates.
        DB::table('settings')->insert([
            'contact_phone' => '+91 89800 04451',
            'contact_email' => 'inquiry@mediadekho.com',
            'contact_address' => '1010-1012, 10th Floor, Venus Atlantis Corporate Park, Prahlad Nagar, Ahmedabad, Gujarat 380054',
            'footer_description' => "Media Dekho Pvt Ltd is India's premier Media Aggregator Platform for campaign planning and ad execution across Offline, Digital, Sports, and Corporate Gifting.",
            'facebook_url' => 'https://www.facebook.com/MediaDekho',
            'twitter_url' => 'https://x.com/MediaDekho',
            'linkedin_url' => 'https://in.linkedin.com/company/mediadekho',
            'youtube_url' => 'https://www.youtube.com/channel/MediaDekho',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
