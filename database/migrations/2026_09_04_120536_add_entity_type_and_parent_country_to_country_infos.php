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
        Schema::table('country_infos', function (Blueprint $table) {
            $table->string('entity_type', 50)->nullable()->after('population_year');
            $table->string('parent_country', 100)->nullable()->after('entity_type');
        });
    }

    public function down(): void
    {
        Schema::table('country_infos', function (Blueprint $table) {
            $table->dropColumn(['entity_type', 'parent_country']);
        });
    }
};
