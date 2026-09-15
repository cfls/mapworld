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
        Schema::create('country_sign_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('sigle', 20)->nullable();
            $table->unsignedSmallInteger('annee_de_reconnaissance')->nullable();
            $table->timestamps();

            $table->index(['country_id', 'sigle']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_sign_languages');
    }
};
