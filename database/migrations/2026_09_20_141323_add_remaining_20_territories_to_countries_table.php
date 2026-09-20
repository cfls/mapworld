<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $afrique = DB::table('continents')->where('name', 'Afrique')->value('id');
        $amerique = DB::table('continents')->where('name', 'Amerique')->value('id');
        $asie = DB::table('continents')->where('name', 'Asie')->value('id');
        $europe = DB::table('continents')->where('name', 'Europe')->value('id');
        $oceanie = DB::table('continents')->where('name', 'Oceanie')->value('id');

        if (! $afrique || ! $amerique || ! $asie || ! $europe || ! $oceanie) {
            return;
        }

        $territories = [
            // Régions autonomes portugaises
            ['continent_id' => $europe,   'name' => 'Açores',           'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/acores.svg',          'latitude' => 37.7412,  'longitude' => -25.6756, 'slug' => 'acores'],
            ['continent_id' => $europe,   'name' => 'Madère',           'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/madere.svg',           'latitude' => 32.7607,  'longitude' => -16.9595, 'slug' => 'madere'],

            // Nations constitutives du Royaume-Uni
            ['continent_id' => $europe,   'name' => 'Angleterre',       'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/angleterre.svg',       'latitude' => 52.3555,  'longitude' => -1.1743,  'slug' => 'angleterre'],
            ['continent_id' => $europe,   'name' => 'Écosse',           'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/ecosse.svg',           'latitude' => 56.4907,  'longitude' => -4.2026,  'slug' => 'ecosse'],
            ['continent_id' => $europe,   'name' => 'Pays de Galles',   'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/pays-de-galles.svg',   'latitude' => 52.1307,  'longitude' => -3.7837,  'slug' => 'pays-de-galles'],
            ['continent_id' => $europe,   'name' => 'Irlande du Nord',  'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/irlande-du-nord.svg',  'latitude' => 54.7877,  'longitude' => -6.4923,  'slug' => 'irlande-du-nord'],
            ['continent_id' => $europe,   'name' => 'Grande-Bretagne',  'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/grande-bretagne.svg',  'latitude' => 54.0000,  'longitude' => -2.5000,  'slug' => 'grande-bretagne'],

            // Territoires espagnols
            ['continent_id' => $afrique,  'name' => 'Canaries',         'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/canaries.svg',         'latitude' => 28.2916,  'longitude' => -16.6291, 'slug' => 'canaries'],
            ['continent_id' => $afrique,  'name' => 'Ceuta',            'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/ceuta.svg',            'latitude' => 35.8894,  'longitude' => -5.3213,  'slug' => 'ceuta'],
            ['continent_id' => $afrique,  'name' => 'Melilla',          'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/melilla.svg',          'latitude' => 35.2923,  'longitude' => -2.9381,  'slug' => 'melilla'],

            // Région grecque
            ['continent_id' => $europe,   'name' => 'Crète',            'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/crete.svg',            'latitude' => 35.2401,  'longitude' => 24.8093,  'slug' => 'crete'],

            // Territoires asiatiques sans ISO officiel
            ['continent_id' => $asie,     'name' => 'Kurdistan',        'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/kurdistan.svg',        'latitude' => 36.4103,  'longitude' => 44.3872,  'slug' => 'kurdistan'],
            ['continent_id' => $asie,     'name' => 'Tchétchénie',      'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/tchetchenie.svg',      'latitude' => 43.4023,  'longitude' => 45.7187,  'slug' => 'tchetchenie'],
            ['continent_id' => $asie,     'name' => 'Tibet',            'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/tibet.svg',            'latitude' => 31.6927,  'longitude' => 88.0924,  'slug' => 'tibet'],

            // Territoire séparatiste européen
            ['continent_id' => $europe,   'name' => 'Transnistrie',     'iso2' => null, 'iso3' => null, 'flag_path' => 'flags/transnistrie.svg',     'latitude' => 47.2151,  'longitude' => 29.4638,  'slug' => 'transnistrie'],

            // Territoires avec code ISO 3166-1
            ['continent_id' => $amerique, 'name' => 'Curaçao',          'iso2' => 'CW', 'iso3' => 'CUW', 'flag_path' => null, 'latitude' => 12.1091,  'longitude' => -68.9316, 'slug' => 'curacao'],
            ['continent_id' => $amerique, 'name' => 'Aruba',            'iso2' => 'AW', 'iso3' => 'ABW', 'flag_path' => null, 'latitude' => 12.5186,  'longitude' => -70.0358, 'slug' => 'aruba'],
            ['continent_id' => $amerique, 'name' => 'Saint-Barthélemy', 'iso2' => 'BL', 'iso3' => 'BLM', 'flag_path' => null, 'latitude' => 17.8967,  'longitude' => -62.8508, 'slug' => 'saint-barthelemy'],
            ['continent_id' => $amerique, 'name' => 'Saint-Martin',     'iso2' => 'MF', 'iso3' => 'MAF', 'flag_path' => null, 'latitude' => 18.0752,  'longitude' => -63.0603, 'slug' => 'saint-martin'],
            ['continent_id' => $oceanie,  'name' => 'Nouvelle-Calédonie', 'iso2' => 'NC', 'iso3' => 'NCL', 'flag_path' => null, 'latitude' => -22.2764, 'longitude' => 166.4572, 'slug' => 'nouvelle-caledonie'],
        ];

        $now = now();
        DB::table('countries')->insert(
            array_map(fn ($t) => array_merge($t, ['created_at' => $now, 'updated_at' => $now]), $territories)
        );
    }

    public function down(): void
    {
        DB::table('countries')->whereIn('iso3', ['CUW', 'ABW', 'BLM', 'MAF', 'NCL'])->delete();

        DB::table('countries')->whereIn('name', [
            'Açores', 'Madère', 'Angleterre', 'Écosse', 'Pays de Galles',
            'Irlande du Nord', 'Grande-Bretagne', 'Canaries', 'Ceuta', 'Melilla',
            'Crète', 'Kurdistan', 'Tchétchénie', 'Tibet', 'Transnistrie',
        ])->whereNull('iso3')->delete();
    }
};
