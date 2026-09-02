<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $amerique = DB::table('continents')->where('name', 'Amerique')->value('id');
        $oceanie = DB::table('continents')->where('name', 'Oceanie')->value('id');

        $territories = [
            [
                'continent_id' => $amerique,
                'name' => 'Curaçao',
                'iso2' => 'CW',
                'iso3' => 'CUW',
                'latitude' => 12.1091,
                'longitude' => -68.9316,
                'slug' => 'curacao',
            ],
            [
                'continent_id' => $amerique,
                'name' => 'Aruba',
                'iso2' => 'AW',
                'iso3' => 'ABW',
                'latitude' => 12.5186,
                'longitude' => -70.0358,
                'slug' => 'aruba',
            ],
            [
                'continent_id' => $amerique,
                'name' => 'Saint-Barthélemy',
                'iso2' => 'BL',
                'iso3' => 'BLM',
                'latitude' => 17.8967,
                'longitude' => -62.8508,
                'slug' => 'saint-barthelemy',
            ],
            [
                'continent_id' => $amerique,
                'name' => 'Saint-Martin',
                'iso2' => 'MF',
                'iso3' => 'MAF',
                'latitude' => 18.0752,
                'longitude' => -63.0603,
                'slug' => 'saint-martin',
            ],
            [
                'continent_id' => $oceanie,
                'name' => 'Nouvelle-Calédonie',
                'iso2' => 'NC',
                'iso3' => 'NCL',
                'latitude' => -22.2764,
                'longitude' => 166.4572,
                'slug' => 'nouvelle-caledonie',
            ],
        ];

        $now = now();

        DB::table('countries')->insert(
            array_map(fn ($t) => array_merge($t, [
                'created_at' => $now,
                'updated_at' => $now,
            ]), $territories)
        );
    }

    public function down(): void
    {
        DB::table('countries')->whereIn('iso3', ['CUW', 'ABW', 'BLM', 'MAF', 'NCL'])->delete();
    }
};
