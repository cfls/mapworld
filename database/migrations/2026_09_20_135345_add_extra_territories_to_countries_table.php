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
            ['continent_id' => $europe,   'name' => 'Gibraltar',            'iso2' => 'GI', 'iso3' => 'GIB', 'latitude' => 36.1408,  'longitude' => -5.3536,  'slug' => 'gibraltar'],
            ['continent_id' => $amerique, 'name' => 'Groenland',            'iso2' => 'GL', 'iso3' => 'GRL', 'latitude' => 72.0000,  'longitude' => -40.0000,  'slug' => 'groenland'],
            ['continent_id' => $amerique, 'name' => 'Guadeloupe',           'iso2' => 'GP', 'iso3' => 'GLP', 'latitude' => 16.9950,  'longitude' => -62.0670,  'slug' => 'guadeloupe'],
            ['continent_id' => $oceanie,  'name' => 'Guam',                 'iso2' => 'GU', 'iso3' => 'GUM', 'latitude' => 13.4443,  'longitude' => 144.7937,  'slug' => 'guam'],
            ['continent_id' => $amerique, 'name' => 'Guyane Française',     'iso2' => 'GF', 'iso3' => 'GUF', 'latitude' => 3.9339,  'longitude' => -53.1258,  'slug' => 'guyane-francaise'],
            ['continent_id' => $asie,     'name' => 'Hong Kong',            'iso2' => 'HK', 'iso3' => 'HKG', 'latitude' => 22.3193,  'longitude' => 114.1694,  'slug' => 'hong-kong'],
            ['continent_id' => $europe,   'name' => 'Île de Man',           'iso2' => 'IM', 'iso3' => 'IMN', 'latitude' => 54.2361,  'longitude' => -4.5481,  'slug' => 'ile-de-man'],
            ['continent_id' => $europe,   'name' => 'Îles Féroé',           'iso2' => 'FO', 'iso3' => 'FRO', 'latitude' => 61.8926,  'longitude' => -6.9118,  'slug' => 'iles-feroe'],
            ['continent_id' => $amerique, 'name' => 'Îles Malouines',       'iso2' => 'FK', 'iso3' => 'FLK', 'latitude' => -51.7963,  'longitude' => -59.5236,  'slug' => 'iles-malouines'],
            ['continent_id' => $afrique,  'name' => 'La Réunion',           'iso2' => 'RE', 'iso3' => 'REU', 'latitude' => -21.1151,  'longitude' => 55.5364,  'slug' => 'la-reunion'],
            ['continent_id' => $asie,     'name' => 'Macao',                'iso2' => 'MO', 'iso3' => 'MAC', 'latitude' => 22.1987,  'longitude' => 113.5439,  'slug' => 'macao'],
            ['continent_id' => $oceanie,  'name' => 'Mariannes du Nord',    'iso2' => 'MP', 'iso3' => 'MNP', 'latitude' => 17.3309,  'longitude' => 145.3846,  'slug' => 'mariannes-du-nord'],
            ['continent_id' => $amerique, 'name' => 'Martinique',           'iso2' => 'MQ', 'iso3' => 'MTQ', 'latitude' => 14.6415,  'longitude' => -61.0242,  'slug' => 'martinique'],
            ['continent_id' => $afrique,  'name' => 'Mayotte',              'iso2' => 'YT', 'iso3' => 'MYT', 'latitude' => -12.8275,  'longitude' => 45.1662,  'slug' => 'mayotte'],
            ['continent_id' => $oceanie,  'name' => 'Polynésie française',  'iso2' => 'PF', 'iso3' => 'PYF', 'latitude' => -17.6797,  'longitude' => -149.4068,  'slug' => 'polynesie-francaise'],
            ['continent_id' => $amerique, 'name' => 'Porto Rico',           'iso2' => 'PR', 'iso3' => 'PRI', 'latitude' => 18.2208,  'longitude' => -66.5901,  'slug' => 'porto-rico'],
            ['continent_id' => $afrique,  'name' => 'Sahara Occidental',    'iso2' => 'EH', 'iso3' => 'ESH', 'latitude' => 24.2155,  'longitude' => -12.8858,  'slug' => 'sahara-occidental'],
            ['continent_id' => $oceanie,  'name' => 'Samoa américaines',    'iso2' => 'AS', 'iso3' => 'ASM', 'latitude' => -14.2710,  'longitude' => -170.1322,  'slug' => 'samoa-americaines'],
            ['continent_id' => $asie,     'name' => 'Taïwan',               'iso2' => 'TW', 'iso3' => 'TWN', 'latitude' => 23.6978,  'longitude' => 120.9605,  'slug' => 'taiwan'],
        ];

        $now = now();
        DB::table('countries')->insert(
            array_map(fn ($t) => array_merge($t, ['created_at' => $now, 'updated_at' => $now]), $territories)
        );
    }

    public function down(): void
    {
        DB::table('countries')->whereIn('iso3', [
            'GIB', 'GRL', 'GLP', 'GUM', 'GUF', 'HKG', 'IMN', 'FRO',
            'FLK', 'REU', 'MAC', 'MNP', 'MTQ', 'MYT', 'PYF', 'PRI',
            'ESH', 'ASM', 'TWN',
        ])->delete();
    }
};
