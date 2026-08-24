<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_inventory_key_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('media_inventory')->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('inventory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_inventory_key_insights');
    }
};
