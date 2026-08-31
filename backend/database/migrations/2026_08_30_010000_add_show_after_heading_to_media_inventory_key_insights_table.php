<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_inventory_key_insights', function (Blueprint $table) {
            // Lets an admin pick a handful of insights (e.g. Frequency,
            // Language, Reach) to surface right under the listing title,
            // alongside the frequency/language pills, instead of only in
            // the full key-insights grid further down the page.
            $table->boolean('show_after_heading')->default(false)->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('media_inventory_key_insights', function (Blueprint $table) {
            $table->dropColumn('show_after_heading');
        });
    }
};
