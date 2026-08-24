<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_inventory_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('media_inventory')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('available')->comment('available, booked, or blocked');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['inventory_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_inventory_availability');
    }
};
