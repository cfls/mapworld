<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marine_area_country', function (Blueprint $table) {
            $table->foreignId('marine_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->primary(['marine_area_id', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marine_area_country');
    }
};
