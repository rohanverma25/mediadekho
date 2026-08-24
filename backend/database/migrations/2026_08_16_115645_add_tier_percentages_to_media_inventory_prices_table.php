<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_inventory_prices', function (Blueprint $table) {
            $table->decimal('retail_percentage', 5, 2)->default(0)->after('base_price');
            $table->decimal('b2c_percentage', 5, 2)->default(0)->after('retail_percentage');
            $table->decimal('b2b_percentage', 5, 2)->default(0)->after('b2c_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('media_inventory_prices', function (Blueprint $table) {
            $table->dropColumn(['retail_percentage', 'b2c_percentage', 'b2b_percentage']);
        });
    }
};
