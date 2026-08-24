<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('razorpay_key_id')->nullable()->after('youtube_url');
            $table->text('razorpay_key_secret')->nullable()->after('razorpay_key_id');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['razorpay_key_id', 'razorpay_key_secret']);
        });
    }
};
