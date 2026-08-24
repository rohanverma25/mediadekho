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
        Schema::dropIfExists('failed_import_rows');
        Schema::dropIfExists('imports');
        Schema::dropIfExists('exports');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally irreversible — these tables belonged to Filament's
        // import/export tracking, which has been removed from the app.
    }
};
