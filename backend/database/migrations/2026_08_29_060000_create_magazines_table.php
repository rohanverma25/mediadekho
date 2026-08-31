<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magazines', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('description', 500)->nullable();
            $table->string('cover_image')->nullable();
            // The PDF itself — read inline via the Magazine Reader page,
            // never treated as a downloadable "document" attachment the
            // way MediaInventoryFile is.
            $table->string('pdf_file');
            $table->date('published_at')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magazines');
    }
};
