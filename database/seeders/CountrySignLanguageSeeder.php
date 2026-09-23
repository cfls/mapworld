<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountrySignLanguage;
use Illuminate\Database\Seeder;

class CountrySignLanguageSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(base_path('docs/referencias/langues_des_signes.json'));
        $records = json_decode($json, true);

        $aliases = [
            'Birmanie (Myanmar)' => 'Myanmar',
            'Biélorussie' => 'Bélarus',
            'Cabo Verde' => 'Cap-Vert',
            'Congo (Brazzaville)' => 'Congo',
            'El Salvador' => 'Salvador',
            'Liberia' => 'Libéria',
            'Îles Salomon' => 'Salomon',
        ];

        $countriesByName = Country::all()->keyBy('name');

        foreach ($records as $record) {
            $pays = $aliases[$record['pays']] ?? $record['pays'];
            $country = $countriesByName[$pays] ?? null;

            if (! $country) {
                $this->command->warn("Pays introuvable : {$record['pays']}");

                continue;
            }

            CountrySignLanguage::updateOrCreate(
                ['country_id' => $country->id],
                [
                    'name' => $record['langue'],
                    'year_official' => $record['annee'] ?: null,
                ]
            );
        }
    }
}
