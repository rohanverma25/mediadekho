<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_listing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone');
            $table->string('media_title');
            $table->string('media_type')->nullable();
            $table->string('location')->nullable();
            // Free-text rather than a strict decimal — vendors describe
            // rates in all sorts of shapes ("₹50,000/month", "on request",
            // a range), and this is a lead to follow up on, not a price
            // MediaInventoryPrice/PricingService will ever compute from.
            $table->string('approximate_rate')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('media_kit')->nullable();
            $table->string('media_kit_original_name')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_listing_requests');
    }
};
