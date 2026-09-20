<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marine_areas', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('marine_areas')
                ->nullOnDelete();
            $table->enum('ocean_group', ['pacifique', 'atlantique', 'indien', 'arctique', 'austral'])
                ->nullable()
                ->after('type');
            $table->bigInteger('surface_km2')->nullable()->after('ocean_group');
            $table->integer('max_depth_m')->nullable()->after('surface_km2');
        });
    }

    public function down(): void
    {
        Schema::table('marine_areas', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'ocean_group', 'surface_km2', 'max_depth_m']);
        });
    }
};
