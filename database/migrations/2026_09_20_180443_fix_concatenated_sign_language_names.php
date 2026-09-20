<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Belgium: row held all three languages concatenated.
        // VGT and DGS already have separate rows — rename this one to LSFB only.
        DB::table('country_sign_languages')
            ->where('name', 'LSFB (Communauté française), VGT (Flandre), DGS (Communauté germanophone)')
            ->update(['name' => 'Langue des signes française de Belgique (LSFB)']);

        // Canada: row held ASL + LSQ + indigenous concatenated.
        // LSQ already has its own row — rename this one to ASL only, then add indigenous.
        $canada = DB::table('countries')->where('name', 'Canada')->value('id');

        DB::table('country_sign_languages')
            ->where('name', 'ASL, langue des signes québécoise (LSQ) et langues des signes autochtones')
            ->update(['name' => 'Langue des signes américaine (ASL)']);

        if ($canada) {
            DB::table('country_sign_languages')->insert([
                'country_id' => $canada,
                'name' => 'Langues des signes autochtones',
                'year_official' => 2019,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // UK: row held BSL + ISL concatenated — rename to BSL only, add ISL separately.
        $uk = DB::table('countries')->where('name', 'Royaume-Uni')->value('id');

        DB::table('country_sign_languages')
            ->where('name', 'Langue des signes britannique (BSL) et langue des signes irlandaise (ISL, Irlande du Nord)')
            ->update(['name' => 'Langue des signes britannique (BSL)']);

        if ($uk) {
            DB::table('country_sign_languages')->insert([
                'country_id' => $uk,
                'name' => 'Langue des signes irlandaise (ISL)',
                'year_official' => 2022,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally empty — restoring concatenated names is not meaningful.
    }
};
