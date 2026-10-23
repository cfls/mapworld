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
            $table->string('plat_typique')->nullable()->after('parent_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('country_infos', function (Blueprint $table) {
            $table->dropColumn('plat_typique');
        });
    }
};
