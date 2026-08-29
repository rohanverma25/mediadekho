<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('source_type')->default('youtube')->after('title');
            $table->string('video_path')->nullable()->after('youtube_url');
            $table->string('thumbnail_path')->nullable()->after('video_path');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'video_path', 'thumbnail_path']);
        });
    }
};
