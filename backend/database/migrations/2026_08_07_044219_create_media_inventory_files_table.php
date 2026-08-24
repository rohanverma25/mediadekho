<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_inventory_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('media_inventory')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size')->comment('Size in bytes');
            $table->timestamps();

            $table->index('inventory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_inventory_files');
    }
};
