<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('contact_emails')->nullable()->after('contact_email');
            $table->json('contact_addresses')->nullable()->after('contact_address');
            $table->string('map_embed_url')->nullable()->after('contact_addresses');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['contact_emails', 'contact_addresses', 'map_embed_url']);
        });
    }
};
