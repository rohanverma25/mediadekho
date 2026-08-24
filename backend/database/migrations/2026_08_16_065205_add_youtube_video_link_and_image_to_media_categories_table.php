<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media_categories', function (Blueprint $table) {
            $table->string('youtube_video_link')->nullable()->after('description');
            $table->string('image')->nullable()->after('youtube_video_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_categories', function (Blueprint $table) {
            $table->dropColumn(['youtube_video_link', 'image']);
        });
    }
};
