<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_inventory', function (Blueprint $table) {
            $table->foreignId('frequency_id')->nullable()->after('subcategory_id')->constrained('frequencies')->nullOnDelete();
            $table->foreignId('language_id')->nullable()->after('frequency_id')->constrained('languages')->nullOnDelete();
            $table->string('short_description', 500)->nullable()->after('title');
            $table->string('image')->nullable()->after('description');
        });

        Schema::table('media_inventory', function (Blueprint $table) {
            $table->dropUnique(['media_name']);
            $table->dropIndex(['city']);
            $table->dropIndex(['featured']);

            $table->dropColumn([
                'media_name',
                'media_type',
                'publication',
                'language',
                'country',
                'state',
                'city',
                'location',
                'audience',
                'industry',
                'format',
                'lead_time',
                'available_from',
                'available_till',
                'availability',
                'featured',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('media_inventory', function (Blueprint $table) {
            $table->dropConstrainedForeignId('frequency_id');
            $table->dropConstrainedForeignId('language_id');
            $table->dropColumn(['short_description', 'image']);

            $table->string('media_name')->unique()->after('title');
            $table->string('media_type')->after('media_name');
            $table->string('publication')->nullable();
            $table->string('language')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('location')->nullable();
            $table->string('audience')->nullable();
            $table->string('industry')->nullable();
            $table->string('format')->nullable();
            $table->unsignedInteger('lead_time')->nullable();
            $table->date('available_from')->nullable();
            $table->date('available_till')->nullable();
            $table->string('availability')->default('available');
            $table->boolean('featured')->default(false);

            $table->index('city');
            $table->index('featured');
        });
    }
};
