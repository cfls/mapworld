<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Belgique: VGT et DGS (communauté germanophone) — production les a séparées en 3 rows.
        $belgique = DB::table('countries')->where('name', 'Belgique')->value('id');

        // Canada: LSQ — production a séparé ASL et LSQ en 2 rows.
        $canada = DB::table('countries')->where('name', 'Canada')->value('id');

        // Espagne: LSC — production a séparé LSE et LSC en 2 rows.
        $espagne = DB::table('countries')->where('name', 'Espagne')->value('id');

        // Taïwan: entry absente en local (local ID ≠ production ID).
        $taiwan = DB::table('countries')->where('name', 'Taïwan')->value('id');

        $rows = array_filter([
            $belgique ? ['country_id' => $belgique, 'name' => 'Langue des signes flamande (VGT)',                          'year_official' => 2006, 'created_at' => $now, 'updated_at' => $now] : null,
            $belgique ? ['country_id' => $belgique, 'name' => 'Langue des signes de la Communauté germanophone (DGS)',     'year_official' => 2019, 'created_at' => $now, 'updated_at' => $now] : null,
            $canada ? ['country_id' => $canada,   'name' => 'Langue des signes québécoise (LSQ)',                        'year_official' => 2019, 'created_at' => $now, 'updated_at' => $now] : null,
            $espagne ? ['country_id' => $espagne,  'name' => 'Langue des signes catalane (LSC)',                          'year_official' => 2010, 'created_at' => $now, 'updated_at' => $now] : null,
            $taiwan ? ['country_id' => $taiwan,   'name' => 'Langue des signes taïwanaise (TSL)',                        'year_official' => 2019, 'created_at' => $now, 'updated_at' => $now] : null,
        ]);

        if ($rows) {
            DB::table('country_sign_languages')->insert(array_values($rows));
        }
    }

    public function down(): void
    {
        $belgique = DB::table('countries')->where('name', 'Belgique')->value('id');
        $canada = DB::table('countries')->where('name', 'Canada')->value('id');
        $espagne = DB::table('countries')->where('name', 'Espagne')->value('id');
        $taiwan = DB::table('countries')->where('name', 'Taïwan')->value('id');

        DB::table('country_sign_languages')
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('country_id', $belgique)->whereIn('name', [
                    'Langue des signes flamande (VGT)',
                    'Langue des signes de la Communauté germanophone (DGS)',
                ]))
                ->orWhere(fn ($q) => $q->where('country_id', $canada)->where('name', 'Langue des signes québécoise (LSQ)'))
                ->orWhere(fn ($q) => $q->where('country_id', $espagne)->where('name', 'Langue des signes catalane (LSC)'))
                ->orWhere(fn ($q) => $q->where('country_id', $taiwan)->where('name', 'Langue des signes taïwanaise (TSL)'))
            )
            ->delete();
    }
};
