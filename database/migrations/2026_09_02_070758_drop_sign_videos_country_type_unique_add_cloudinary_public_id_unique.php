<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sign_videos', function (Blueprint $table) {
            // Add a plain index on country_id so MySQL FK constraint is satisfied
            // before we drop the composite unique that was serving as that index.
            $table->index('country_id');
        });

        Schema::table('sign_videos', function (Blueprint $table) {
            $table->dropUnique('sign_videos_country_id_type_unique');
            $table->unique('cloudinary_public_id');
        });
    }

    public function down(): void
    {
        Schema::table('sign_videos', function (Blueprint $table) {
            $table->dropUnique(['cloudinary_public_id']);
            $table->unique(['country_id', 'type']);
            $table->dropIndex(['country_id']);
        });
    }
};
