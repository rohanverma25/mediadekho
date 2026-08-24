<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->longText('privacy_policy')->nullable()->after('footer_scripts');
            $table->longText('terms_of_use')->nullable()->after('privacy_policy');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['privacy_policy', 'terms_of_use']);
        });
    }
};
