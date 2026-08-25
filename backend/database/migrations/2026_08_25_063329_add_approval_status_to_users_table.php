<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // B2B self-registrations start 'pending' and can't log in until
            // an admin approves them (see AuthController). Every other
            // account type is approved instantly, so 'approved' is the
            // default for the column as a whole — existing rows and every
            // other registration path stay unaffected.
            $table->string('approval_status')->default('approved')->after('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
