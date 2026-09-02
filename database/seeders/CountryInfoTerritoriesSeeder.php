<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryInfo;
use Illuminate\Database\Seeder;

/**
 * Seed CountryInfo pour le Kosovo et les 15 régions/territoires exclus de
 * l'import RESTCountries (`countries:import-infos`) parce qu'ils n'ont pas de
 * code ISO 3166-1 alpha-3 officiel ou renvoient une réponse vide.
 *
 * Sources : recensements nationaux / estimations Wikipédia (2021-2024).
 * Ajuster les valeurs si les autorités locales publient des chiffres plus récents.
 */
class CountryInfoTerritoriesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $record) {
            $country = Country::query()->where('name', $record['name'])->first();

            if ($country === null) {
                $this->command?->warn("Pays introuvable : {$record['name']}");

                continue;
            }

            CountryInfo::updateOrCreate(
                ['country_id' => $country->id],
                [
                    'capital' => $record['capital'],
                    'languages' => $record['languages'],
                    'population' => $record['population'],
                    'currency' => $record['currency'],
                    'population_year' => $record['population_year'],
                ],
            );
        }
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     capital: ?string,
     *     languages: ?array<int, array{code: ?string, name: string, native_name: ?string}>,
     *     population: ?int,
     *     currency: ?string,
     *     population_year: ?int
     * }>
     */
    private function records(): array
    {
        return [
            // Kosovo — reconnu par de nombreux États mais ISO XKX non officiel.
            [
                'name' => 'Kosovo',
                'capital' => 'Pristina',
                'languages' => [
                    ['code' => 'sq', 'name' => 'Albanian', 'native_name' => 'shqip'],
                    ['code' => 'sr', 'name' => 'Serbian', 'native_name' => 'српски'],
                ],
                'population' => 1_761_985,
                'currency' => 'EUR',
                'population_year' => 2024,
            ],

            // Nations constitutives du Royaume-Uni.
            [
                'name' => 'Angleterre',
                'capital' => 'Londres',
                'languages' => [
                    ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
                ],
                'population' => 56_536_000,
                'currency' => 'GBP',
                'population_year' => 2021,
            ],
            [
                'name' => 'Écosse',
                'capital' => 'Édimbourg',
                'languages' => [
                    ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
                    ['code' => 'gd', 'name' => 'Scottish Gaelic', 'native_name' => 'Gàidhlig'],
                    ['code' => 'sco', 'name' => 'Scots', 'native_name' => 'Scots'],
                ],
                'population' => 5_479_900,
                'currency' => 'GBP',
                'population_year' => 2022,
            ],
            [
                'name' => 'Pays de Galles',
                'capital' => 'Cardiff',
                'languages' => [
                    ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
                    ['code' => 'cy', 'name' => 'Welsh', 'native_name' => 'Cymraeg'],
                ],
                'population' => 3_131_640,
                'currency' => 'GBP',
                'population_year' => 2022,
            ],
            [
                'name' => 'Irlande du Nord',
                'capital' => 'Belfast',
                'languages' => [
                    ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
                    ['code' => 'ga', 'name' => 'Irish', 'native_name' => 'Gaeilge'],
                ],
                'population' => 1_910_543,
                'currency' => 'GBP',
                'population_year' => 2022,
            ],
            [
                'name' => 'Grande-Bretagne',
                'capital' => 'Londres',
                'languages' => [
                    ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
                ],
                'population' => 65_147_540,
                'currency' => 'GBP',
                'population_year' => 2022,
            ],

            // Territoires espagnols hors péninsule.
            [
                'name' => 'Canaries',
                'capital' => 'Santa Cruz de Tenerife',
                'languages' => [
                    ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'español'],
                ],
                'population' => 2_213_016,
                'currency' => 'EUR',
                'population_year' => 2023,
            ],
            [
                'name' => 'Ceuta',
                'capital' => 'Ceuta',
                'languages' => [
                    ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'español'],
                ],
                'population' => 83_517,
                'currency' => 'EUR',
                'population_year' => 2023,
            ],
            [
                'name' => 'Melilla',
                'capital' => 'Melilla',
                'languages' => [
                    ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'español'],
                ],
                'population' => 86_384,
                'currency' => 'EUR',
                'population_year' => 2023,
            ],

            // Régions autonomes portugaises.
            [
                'name' => 'Açores',
                'capital' => 'Ponta Delgada',
                'languages' => [
                    ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'português'],
                ],
                'population' => 236_413,
                'currency' => 'EUR',
                'population_year' => 2023,
            ],
            [
                'name' => 'Madère',
                'capital' => 'Funchal',
                'languages' => [
                    ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'português'],
                ],
                'population' => 250_744,
                'currency' => 'EUR',
                'population_year' => 2023,
            ],

            // Région grecque.
            [
                'name' => 'Crète',
                'capital' => 'Héraklion',
                'languages' => [
                    ['code' => 'el', 'name' => 'Greek', 'native_name' => 'ελληνικά'],
                ],
                'population' => 617_360,
                'currency' => 'EUR',
                'population_year' => 2021,
            ],

            // République russe.
            [
                'name' => 'Tchétchénie',
                'capital' => 'Grozny',
                'languages' => [
                    ['code' => 'ce', 'name' => 'Chechen', 'native_name' => 'нохчийн мотт'],
                    ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'русский'],
                ],
                'population' => 1_510_824,
                'currency' => 'RUB',
                'population_year' => 2021,
            ],

            // Région autonome chinoise.
            [
                'name' => 'Tibet',
                'capital' => 'Lhassa',
                'languages' => [
                    ['code' => 'bo', 'name' => 'Tibetan', 'native_name' => 'བོད་སྐད་'],
                    ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文'],
                ],
                'population' => 3_648_100,
                'currency' => 'CNY',
                'population_year' => 2022,
            ],

            // Territoire séparatiste moldave — le rouble transnistrien n'a pas de code ISO 4217.
            [
                'name' => 'Transnistrie',
                'capital' => 'Tiraspol',
                'languages' => [
                    ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'русский'],
                    ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'українська'],
                    ['code' => 'ro', 'name' => 'Romanian', 'native_name' => 'română'],
                ],
                'population' => 347_251,
                'currency' => null,
                'population_year' => 2023,
            ],

            // Kurdistan — région transfrontalière ; chiffres du Kurdistan irakien (Région autonome).
            [
                'name' => 'Kurdistan',
                'capital' => 'Erbil',
                'languages' => [
                    ['code' => 'ku', 'name' => 'Kurdish', 'native_name' => 'کوردی'],
                ],
                'population' => 6_171_000,
                'currency' => 'IQD',
                'population_year' => 2024,
            ],
        ];
    }
}
