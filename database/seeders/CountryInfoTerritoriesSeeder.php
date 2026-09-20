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
                    'currency_code' => $record['currency_code'],
                    'population_year' => $record['population_year'],
                    'entity_type' => $record['entity_type'],
                    'parent_country' => $record['parent_country'],
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
     *     currency_code: ?string,
     *     population_year: ?int,
     *     entity_type: ?string,
     *     parent_country: ?string
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
                    ['code' => 'sq', 'name' => 'Albanais', 'native_name' => 'shqip'],
                    ['code' => 'sr', 'name' => 'Serbe', 'native_name' => 'српски'],
                ],
                'population' => 1_761_985,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2024,
                'entity_type' => 'sovereign_state',
                'parent_country' => null,
            ],

            // Nations constitutives du Royaume-Uni.
            [
                'name' => 'Angleterre',
                'capital' => 'Londres',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                ],
                'population' => 56_536_000,
                'currency' => 'livre sterling',
                'currency_code' => 'GBP',
                'population_year' => 2021,
                'entity_type' => 'constituent_country',
                'parent_country' => 'Royaume-Uni',
            ],
            [
                'name' => 'Écosse',
                'capital' => 'Édimbourg',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'gd', 'name' => 'Gaélique écossais', 'native_name' => 'Gàidhlig'],
                    ['code' => 'sco', 'name' => 'Scots', 'native_name' => 'Scots'],
                ],
                'population' => 5_479_900,
                'currency' => 'livre sterling',
                'currency_code' => 'GBP',
                'population_year' => 2022,
                'entity_type' => 'constituent_country',
                'parent_country' => 'Royaume-Uni',
            ],
            [
                'name' => 'Pays de Galles',
                'capital' => 'Cardiff',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'cy', 'name' => 'Gallois', 'native_name' => 'Cymraeg'],
                ],
                'population' => 3_131_640,
                'currency' => 'livre sterling',
                'currency_code' => 'GBP',
                'population_year' => 2022,
                'entity_type' => 'constituent_country',
                'parent_country' => 'Royaume-Uni',
            ],
            [
                'name' => 'Irlande du Nord',
                'capital' => 'Belfast',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'ga', 'name' => 'Irlandais', 'native_name' => 'Gaeilge'],
                ],
                'population' => 1_910_543,
                'currency' => 'livre sterling',
                'currency_code' => 'GBP',
                'population_year' => 2022,
                'entity_type' => 'constituent_country',
                'parent_country' => 'Royaume-Uni',
            ],
            [
                'name' => 'Grande-Bretagne',
                'capital' => 'Londres',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                ],
                'population' => 65_147_540,
                'currency' => 'livre sterling',
                'currency_code' => 'GBP',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'Royaume-Uni',
            ],

            // Territoires espagnols hors péninsule.
            [
                'name' => 'Canaries',
                'capital' => 'Santa Cruz de Tenerife',
                'languages' => [
                    ['code' => 'es', 'name' => 'Espagnol', 'native_name' => 'español'],
                ],
                'population' => 2_213_016,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2023,
                'entity_type' => 'autonomous_community',
                'parent_country' => 'Espagne',
            ],
            [
                'name' => 'Ceuta',
                'capital' => 'Ceuta',
                'languages' => [
                    ['code' => 'es', 'name' => 'Espagnol', 'native_name' => 'español'],
                ],
                'population' => 83_517,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2023,
                'entity_type' => 'autonomous_city',
                'parent_country' => 'Espagne',
            ],
            [
                'name' => 'Melilla',
                'capital' => 'Melilla',
                'languages' => [
                    ['code' => 'es', 'name' => 'Espagnol', 'native_name' => 'español'],
                ],
                'population' => 86_384,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2023,
                'entity_type' => 'autonomous_city',
                'parent_country' => 'Espagne',
            ],

            // Régions autonomes portugaises.
            [
                'name' => 'Açores',
                'capital' => 'Ponta Delgada',
                'languages' => [
                    ['code' => 'pt', 'name' => 'Portugais', 'native_name' => 'português'],
                ],
                'population' => 236_413,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2023,
                'entity_type' => 'autonomous_region',
                'parent_country' => 'Portugal',
            ],
            [
                'name' => 'Madère',
                'capital' => 'Funchal',
                'languages' => [
                    ['code' => 'pt', 'name' => 'Portugais', 'native_name' => 'português'],
                ],
                'population' => 250_744,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2023,
                'entity_type' => 'autonomous_region',
                'parent_country' => 'Portugal',
            ],

            // Région grecque.
            [
                'name' => 'Crète',
                'capital' => 'Héraklion',
                'languages' => [
                    ['code' => 'el', 'name' => 'Grec', 'native_name' => 'ελληνικά'],
                ],
                'population' => 617_360,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2021,
                'entity_type' => 'administrative_region',
                'parent_country' => 'Grèce',
            ],

            // République russe.
            [
                'name' => 'Tchétchénie',
                'capital' => 'Grozny',
                'languages' => [
                    ['code' => 'ce', 'name' => 'Tchétchène', 'native_name' => 'нохчийн мотт'],
                    ['code' => 'ru', 'name' => 'Russe', 'native_name' => 'русский'],
                ],
                'population' => 1_510_824,
                'currency' => 'rouble russe',
                'currency_code' => 'RUB',
                'population_year' => 2021,
                'entity_type' => 'federal_subject',
                'parent_country' => 'Russie',
            ],

            // Région autonome chinoise.
            [
                'name' => 'Tibet',
                'capital' => 'Lhassa',
                'languages' => [
                    ['code' => 'bo', 'name' => 'Tibétain', 'native_name' => 'བོད་སྐད་'],
                    ['code' => 'zh', 'name' => 'Chinois', 'native_name' => '中文'],
                ],
                'population' => 3_648_100,
                'currency' => 'yuan renminbi',
                'currency_code' => 'CNY',
                'population_year' => 2022,
                'entity_type' => 'autonomous_region',
                'parent_country' => 'Chine',
            ],

            // Territoire séparatiste moldave — le rouble transnistrien n'a pas de code ISO 4217.
            [
                'name' => 'Transnistrie',
                'capital' => 'Tiraspol',
                'languages' => [
                    ['code' => 'ru', 'name' => 'Russe', 'native_name' => 'русский'],
                    ['code' => 'uk', 'name' => 'Ukrainien', 'native_name' => 'українська'],
                    ['code' => 'ro', 'name' => 'Roumain', 'native_name' => 'română'],
                ],
                'population' => 347_251,
                'currency' => null,
                'currency_code' => null,
                'population_year' => 2023,
                'entity_type' => 'unrecognized_state',
                'parent_country' => 'Moldavie',
            ],

            // Kurdistan — région transfrontalière ; chiffres du Kurdistan irakien (Région autonome).
            [
                'name' => 'Kurdistan',
                'capital' => 'Erbil',
                'languages' => [
                    ['code' => 'ku', 'name' => 'Kurde', 'native_name' => 'کوردی'],
                ],
                'population' => 6_171_000,
                'currency' => 'dinar irakien',
                'currency_code' => 'IQD',
                'population_year' => 2024,
                'entity_type' => 'transnational_region',
                'parent_country' => null,
            ],

            // Territoires et régions avec code ISO 3166-1 présents en production.
            [
                'name' => 'Gibraltar',
                'capital' => 'Gibraltar',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'es', 'name' => 'Espagnol', 'native_name' => 'español'],
                ],
                'population' => 33_701,
                'currency' => 'livre sterling',
                'currency_code' => 'GBP',
                'population_year' => 2021,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'Royaume-Uni',
            ],
            [
                'name' => 'Groenland',
                'capital' => 'Nuuk',
                'languages' => [
                    ['code' => 'kl', 'name' => 'Groenlandais', 'native_name' => 'Kalaallisut'],
                    ['code' => 'da', 'name' => 'Danois', 'native_name' => 'dansk'],
                ],
                'population' => 56_081,
                'currency' => 'couronne danoise',
                'currency_code' => 'DKK',
                'population_year' => 2023,
                'entity_type' => 'autonomous_region',
                'parent_country' => 'Danemark',
            ],
            [
                'name' => 'Guadeloupe',
                'capital' => 'Basse-Terre',
                'languages' => [
                    ['code' => 'fr', 'name' => 'Français', 'native_name' => 'français'],
                ],
                'population' => 395_700,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'France',
            ],
            [
                'name' => 'Guam',
                'capital' => 'Hagåtña',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'ch', 'name' => 'Chamorro', 'native_name' => 'Chamoru'],
                ],
                'population' => 168_485,
                'currency' => 'dollar américain',
                'currency_code' => 'USD',
                'population_year' => 2023,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'États-Unis',
            ],
            [
                'name' => 'Guyane Française',
                'capital' => 'Cayenne',
                'languages' => [
                    ['code' => 'fr', 'name' => 'Français', 'native_name' => 'français'],
                ],
                'population' => 300_683,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'France',
            ],
            [
                'name' => 'Hong Kong',
                'capital' => 'Hong Kong',
                'languages' => [
                    ['code' => 'zh', 'name' => 'Chinois', 'native_name' => '中文'],
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                ],
                'population' => 7_413_070,
                'currency' => 'dollar de Hong Kong',
                'currency_code' => 'HKD',
                'population_year' => 2023,
                'entity_type' => 'administrative_region',
                'parent_country' => 'Chine',
            ],
            [
                'name' => 'Île de Man',
                'capital' => 'Douglas',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'gv', 'name' => 'Mannois', 'native_name' => 'Gaelg'],
                ],
                'population' => 84_069,
                'currency' => 'livre mannoise',
                'currency_code' => 'GBP',
                'population_year' => 2021,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'Royaume-Uni',
            ],
            [
                'name' => 'Îles Féroé',
                'capital' => 'Tórshavn',
                'languages' => [
                    ['code' => 'fo', 'name' => 'Féroïen', 'native_name' => 'Føroyskt'],
                    ['code' => 'da', 'name' => 'Danois', 'native_name' => 'dansk'],
                ],
                'population' => 54_000,
                'currency' => 'couronne danoise',
                'currency_code' => 'DKK',
                'population_year' => 2023,
                'entity_type' => 'autonomous_region',
                'parent_country' => 'Danemark',
            ],
            [
                'name' => 'Îles Malouines',
                'capital' => 'Stanley',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                ],
                'population' => 3_539,
                'currency' => 'livre des Malouines',
                'currency_code' => 'FKP',
                'population_year' => 2021,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'Royaume-Uni',
            ],
            [
                'name' => 'La Réunion',
                'capital' => 'Saint-Denis',
                'languages' => [
                    ['code' => 'fr', 'name' => 'Français', 'native_name' => 'français'],
                ],
                'population' => 906_000,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'France',
            ],
            [
                'name' => 'Macao',
                'capital' => 'Macao',
                'languages' => [
                    ['code' => 'zh', 'name' => 'Chinois', 'native_name' => '中文'],
                    ['code' => 'pt', 'name' => 'Portugais', 'native_name' => 'português'],
                ],
                'population' => 680_600,
                'currency' => 'pataca',
                'currency_code' => 'MOP',
                'population_year' => 2023,
                'entity_type' => 'administrative_region',
                'parent_country' => 'Chine',
            ],
            [
                'name' => 'Mariannes du Nord',
                'capital' => 'Saipan',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'ch', 'name' => 'Chamorro', 'native_name' => 'Chamoru'],
                ],
                'population' => 47_329,
                'currency' => 'dollar américain',
                'currency_code' => 'USD',
                'population_year' => 2020,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'États-Unis',
            ],
            [
                'name' => 'Martinique',
                'capital' => 'Fort-de-France',
                'languages' => [
                    ['code' => 'fr', 'name' => 'Français', 'native_name' => 'français'],
                ],
                'population' => 349_925,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'France',
            ],
            [
                'name' => 'Mayotte',
                'capital' => 'Mamoudzou',
                'languages' => [
                    ['code' => 'fr', 'name' => 'Français', 'native_name' => 'français'],
                ],
                'population' => 396_000,
                'currency' => 'euro',
                'currency_code' => 'EUR',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'France',
            ],
            [
                'name' => 'Polynésie française',
                'capital' => 'Papeete',
                'languages' => [
                    ['code' => 'fr', 'name' => 'Français', 'native_name' => 'français'],
                    ['code' => 'ty', 'name' => 'Tahitien', 'native_name' => 'Reo Tahiti'],
                ],
                'population' => 278_786,
                'currency' => 'franc CFP',
                'currency_code' => 'XPF',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'France',
            ],
            [
                'name' => 'Porto Rico',
                'capital' => 'San Juan',
                'languages' => [
                    ['code' => 'es', 'name' => 'Espagnol', 'native_name' => 'español'],
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                ],
                'population' => 3_205_691,
                'currency' => 'dollar américain',
                'currency_code' => 'USD',
                'population_year' => 2022,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'États-Unis',
            ],
            [
                'name' => 'Sahara Occidental',
                'capital' => 'El Aaiún',
                'languages' => [
                    ['code' => 'ar', 'name' => 'Arabe', 'native_name' => 'العربية'],
                    ['code' => 'es', 'name' => 'Espagnol', 'native_name' => 'español'],
                ],
                'population' => 600_000,
                'currency' => null,
                'currency_code' => null,
                'population_year' => 2021,
                'entity_type' => 'geographic_entity',
                'parent_country' => null,
            ],
            [
                'name' => 'Samoa américaines',
                'capital' => 'Pago Pago',
                'languages' => [
                    ['code' => 'en', 'name' => 'Anglais', 'native_name' => 'English'],
                    ['code' => 'sm', 'name' => 'Samoan', 'native_name' => 'Gagana Sāmoa'],
                ],
                'population' => 55_000,
                'currency' => 'dollar américain',
                'currency_code' => 'USD',
                'population_year' => 2020,
                'entity_type' => 'geographic_entity',
                'parent_country' => 'États-Unis',
            ],
            [
                'name' => 'Taïwan',
                'capital' => 'Taipei',
                'languages' => [
                    ['code' => 'zh', 'name' => 'Chinois', 'native_name' => '中文'],
                ],
                'population' => 23_570_000,
                'currency' => 'nouveau dollar taïwanais',
                'currency_code' => 'TWD',
                'population_year' => 2023,
                'entity_type' => 'unrecognized_state',
                'parent_country' => null,
            ],
        ];
    }
}
