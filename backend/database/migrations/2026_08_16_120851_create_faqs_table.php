<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('media_categories')->nullOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained('media_inventory')->nullOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('category_id');
            $table->index('inventory_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
