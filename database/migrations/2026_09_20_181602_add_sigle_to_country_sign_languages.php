<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('country_sign_languages', function (Blueprint $table) {
            $table->string('sigle', 20)->nullable()->after('name');
        });

        // Extract acronyms embedded in existing name strings like "Langue des signes xxx (LSE)"
        // and move them into the new sigle column, then clean the name.
        DB::table('country_sign_languages')
            ->where('name', 'like', '%(%)%')
            ->get(['id', 'name'])
            ->each(function ($row) {
                if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/', $row->name, $m)) {
                    DB::table('country_sign_languages')
                        ->where('id', $row->id)
                        ->update(['name' => trim($m[1]), 'sigle' => trim($m[2])]);
                }
            });
    }

    public function down(): void
    {
        // Restore sigle into name before dropping the column.
        DB::table('country_sign_languages')
            ->whereNotNull('sigle')
            ->get(['id', 'name', 'sigle'])
            ->each(function ($row) {
                DB::table('country_sign_languages')
                    ->where('id', $row->id)
                    ->update(['name' => "{$row->name} ({$row->sigle})"]);
            });

        Schema::table('country_sign_languages', function (Blueprint $table) {
            $table->dropColumn('sigle');
        });
    }
};
