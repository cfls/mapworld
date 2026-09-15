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
            $table->string('langue_de_signes')->nullable()->after('parent_country');
            $table->unsignedSmallInteger('annee_de_reconnaissance')->nullable()->after('langue_de_signes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('country_infos', function (Blueprint $table) {
            $table->dropColumn(['langue_de_signes', 'annee_de_reconnaissance']);
        });
    }
};
