<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('media_categories')->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('media_categories')->nullOnDelete();
            $table->string('title');
            $table->string('media_name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('media_type');
            $table->string('publication')->nullable();
            $table->string('language')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('location')->nullable();
            $table->string('audience')->nullable();
            $table->string('industry')->nullable();
            $table->string('format')->nullable();
            $table->unsignedInteger('lead_time')->nullable()->comment('Lead time in days');
            $table->date('available_from')->nullable();
            $table->date('available_till')->nullable();
            $table->string('availability')->default('available');
            $table->string('status')->default('draft');
            $table->boolean('featured')->default(false);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('subcategory_id');
            $table->index('status');
            $table->index('city');
            $table->index('featured');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_inventory');
    }
};
