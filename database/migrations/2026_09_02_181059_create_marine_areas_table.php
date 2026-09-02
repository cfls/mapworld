<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marine_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('geojson_id')->nullable()->index();
            $table->enum('type', ['ocean', 'sea', 'gulf', 'bay', 'other'])->default('ocean');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marine_areas');
    }
};
