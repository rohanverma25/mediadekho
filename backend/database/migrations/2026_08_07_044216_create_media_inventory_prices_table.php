<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_inventory_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->unique()->constrained('media_inventory')->cascadeOnDelete();
            $table->decimal('base_price', 12, 2);
            $table->decimal('retail_price', 12, 2);
            $table->decimal('b2c_price', 12, 2);
            $table->decimal('b2b_price', 12, 2);
            $table->decimal('enterprise_price', 12, 2)->nullable();
            $table->string('discount_type')->nullable()->comment('flat or percentage');
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(0);
            $table->decimal('platform_margin', 12, 2)->default(0);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_inventory_prices');
    }
};
