<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('marine_areas', function (Blueprint $table) {
            $table->boolean('status')->default(false)->after('name');
        });

        // Activar los 8 mares visibles
        DB::table('marine_areas')
            ->whereIn('id', [1, 8, 19, 26, 30, 31, 32, 33])
            ->update(['status' => true]);
    }

    public function down(): void
    {
        Schema::table('marine_areas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
