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
        Schema::create('sign_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('cloudinary_public_id');
            $table->string('cloudinary_url');
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unique(['country_id', 'type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sign_videos');
    }
};
