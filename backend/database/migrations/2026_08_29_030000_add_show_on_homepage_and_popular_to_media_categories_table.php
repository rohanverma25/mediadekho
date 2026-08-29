<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_categories', function (Blueprint $table) {
            // Defaults to true (unlike the "popular" flag below) because
            // every active category already appears on the homepage today —
            // defaulting to false here would silently empty that section
            // for every existing category until an admin opted each one
            // back in.
            $table->boolean('show_on_homepage')->default(true)->after('status');
            $table->boolean('show_on_popular')->default(false)->after('show_on_homepage');
        });
    }

    public function down(): void
    {
        Schema::table('media_categories', function (Blueprint $table) {
            $table->dropColumn(['show_on_homepage', 'show_on_popular']);
        });
    }
};
