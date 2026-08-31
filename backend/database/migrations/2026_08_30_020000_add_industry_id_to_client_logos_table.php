<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_logos', function (Blueprint $table) {
            // Nullable — existing logos, and future ones, aren't required to
            // belong to an industry; it just enables the homepage's
            // "click an industry, see its clients" filter when set.
            $table->foreignId('industry_id')->nullable()->after('name')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('client_logos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('industry_id');
        });
    }
};
