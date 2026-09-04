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
            $table->string('currency_code', 10)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('country_infos', function (Blueprint $table) {
            $table->dropColumn('currency_code');
        });
    }
};
