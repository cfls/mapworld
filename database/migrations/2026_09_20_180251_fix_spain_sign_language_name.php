<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('country_sign_languages')
            ->where('name', 'Langue des signes espagnole (LSE) et langue des signes catalane (LSC)')
            ->update(['name' => 'Langue des signes espagnole (LSE)']);
    }

    public function down(): void
    {
        // Intentionally empty — restoring the corrupted concatenated name is not meaningful.
    }
};
