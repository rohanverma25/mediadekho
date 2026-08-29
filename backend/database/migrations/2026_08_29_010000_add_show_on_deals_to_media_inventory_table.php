<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_inventory', function (Blueprint $table) {
            $table->boolean('show_on_deals')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('media_inventory', function (Blueprint $table) {
            $table->dropColumn('show_on_deals');
        });
    }
};
