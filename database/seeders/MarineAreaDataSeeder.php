<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\MarineArea;
use Illuminate\Database\Seeder;

class MarineAreaDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->assignOceanData();
        $this->attachCoastalCountries();
    }

    private function assignOceanData(): void
    {
        // [geojson_id => [ocean_group, parent_geojson_id, surface_km2, max_depth_m]]
        $data = [
            // --- Océans ---
            '1159115017' => ['arctique',   null,           14_060_000,  5625],  // Arctique
            '1159115037' => ['austral',    null,           20_327_000,  7235],  // Austral
            '1159115057' => ['atlantique', null,           41_000_000,  8376],  // Atlantique Nord
            '1159115079' => ['pacifique',  null,           84_000_000, 10911],  // Pacifique Nord
            '1159115099' => ['pacifique',  null,           81_000_000,  9000],  // Pacifique Sud
            '1159115123' => ['indien',     null,           70_560_000,  7258],  // Indien
            '1159115149' => ['atlantique', null,           40_000_000,  9220],  // Atlantique Sud

            // --- Mers & golfs atlantique ---
            '1159115171' => ['atlantique', '1159115321',      436_402,  2212],  // Mer Noire → Méditerranée
            '1159115321' => ['atlantique', '1159115057',    2_510_000,  5267],  // Mer Méditerranée → Atl Nord
            '1159115379' => ['atlantique', '1159115057',    2_754_000,  7686],  // Mer des Caraïbes → Atl Nord
            '1159115399' => ['atlantique', '1159115057',    1_550_000,  4384],  // Golfe du Mexique → Atl Nord
            '1159115419' => ['atlantique', '1159115057',      841_000,  4316],  // Mer du Labrador → Atl Nord

            // --- Mers & baies arctiques ---
            '1159115359' => ['arctique',   '1159115017',      476_000,  4683],  // Mer de Beaufort → Arctique
            '1159115441' => ['arctique',   '1159115017',    1_230_000,   270],  // Baie d'Hudson → Arctique
            '1159115483' => ['arctique',   '1159115017',      689_000,  2136],  // Mer de Baffin → Arctique

            // --- Mers & golfs indien ---
            '1159115253' => ['indien',     '1159115123',    2_172_000,  4694],  // Golfe du Bengale → Indien
            '1159115343' => ['indien',     '1159115123',    3_862_000,  5803],  // Mer d'Arabie → Indien
            '1159115461' => ['indien',     null,              371_000,  1025],  // Mer Caspienne (enclavée)
            '1159115521' => ['indien',     '1159115123',      438_000,  3040],  // Mer Rouge → Indien
            '1159115597' => ['indien',     '1159115123',      251_000,    90],  // Golfe Persique → Indien

            // --- Mers & golfs pacifique ---
            '1159115197' => ['pacifique',  '1159115079',    5_695_000, 10540],  // Mer des Philippines → Pac Nord
            '1159115219' => ['pacifique',  '1159115099',    4_791_000,  9140],  // Mer de Corail → Pac Sud
            '1159115233' => ['pacifique',  '1159115099',    2_300_000,  5943],  // Mer de Tasman → Pac Sud
            '1159115273' => ['pacifique',  '1159115079',    3_500_000,  5560],  // Mer de Chine méridionale → Pac Nord
            '1159115305' => ['pacifique',  '1159115079',    1_007_500,  3742],  // Mer du Japon → Pac Nord
            '1159115503' => ['pacifique',  '1159115079',    1_533_000,  5659],  // Golfe d'Alaska → Pac Nord
            '1159115541' => ['pacifique',  '1159115079',    1_583_000,  3916],  // Mer d'Okhotsk → Pac Nord

            // --- Mers australes ---
            '1159115579' => ['austral',    '1159115037',    2_800_000,  4500],  // Mer de Weddell → Austral
            '1159115615' => ['austral',    '1159115037',      960_000,  4000],  // Mer de Ross → Austral
        ];

        $areas = MarineArea::all()->keyBy('geojson_id');

        foreach ($data as $geojsonId => [$oceanGroup, $parentGeojsonId, $surface, $depth]) {
            $area = $areas->get($geojsonId);
            if (! $area) {
                continue;
            }

            $parentId = $parentGeojsonId ? $areas->get($parentGeojsonId)?->id : null;

            $area->update([
                'ocean_group' => $oceanGroup,
                'parent_id' => $parentId,
                'surface_km2' => $surface,
                'max_depth_m' => $depth,
            ]);
        }
    }

    private function attachCoastalCountries(): void
    {
        // Mapping geojson_id → country names (in French, as stored in DB)
        $coastalMap = [
            '1159115017' => [ // Océan Arctique
                'Canada', 'États-Unis', 'Russie', 'Norvège', 'Danemark', 'Islande',
            ],
            '1159115037' => [// Océan Austral — eaux antarctiques, pas d'États côtiers souverains
            ],
            '1159115057' => [ // Océan Atlantique Nord
                'Canada', 'États-Unis', 'Mexique', 'Brésil', 'Royaume-Uni', 'France',
                'Espagne', 'Portugal', 'Maroc', 'Mauritanie', 'Sénégal', 'Islande',
                'Irlande', 'Norvège',
            ],
            '1159115079' => [ // Océan Pacifique Nord
                'Russie', 'Japon', 'Chine', 'Corée du Nord', 'Corée du Sud',
                'Philippines', 'Canada', 'États-Unis', 'Mexique',
            ],
            '1159115099' => [ // Océan Pacifique Sud
                'Australie', 'Nouvelle-Zélande', 'Fidji', 'Papouasie-Nouvelle-Guinée',
                'Salomon', 'Vanuatu', 'Tonga', 'Samoa', 'Kiribati', 'Tuvalu', 'Nauru',
                'États-Unis', 'Chili', 'Pérou', 'Équateur', 'Colombie', 'Panama', 'Costa Rica',
            ],
            '1159115123' => [ // Océan Indien
                'Inde', 'Pakistan', 'Iran', 'Arabie saoudite', 'Yémen', 'Oman',
                'Émirats arabes unis', 'Kenya', 'Tanzanie', 'Mozambique', 'Afrique du Sud',
                'Madagascar', 'Sri Lanka', 'Bangladesh', 'Myanmar', 'Thaïlande',
                'Indonésie', 'Australie', 'Maldives', 'Seychelles',
            ],
            '1159115149' => [ // Océan Atlantique Sud
                'Brésil', 'Argentine', 'Uruguay', 'Chili', 'Afrique du Sud',
                'Angola', 'Namibie', 'Congo', 'Gabon',
            ],
            '1159115171' => [ // Mer Noire
                'Turquie', 'Bulgarie', 'Roumanie', 'Ukraine', 'Russie', 'Géorgie',
            ],
            '1159115197' => [ // Mer des Philippines
                'Philippines', 'Chine', 'Japon', 'Palaos',
            ],
            '1159115219' => [ // Mer de Corail
                'Australie', 'Papouasie-Nouvelle-Guinée', 'Salomon', 'Vanuatu',
            ],
            '1159115233' => [ // Mer de Tasman
                'Australie', 'Nouvelle-Zélande',
            ],
            '1159115253' => [ // Golfe du Bengale
                'Bangladesh', 'Inde', 'Myanmar', 'Thaïlande', 'Sri Lanka',
            ],
            '1159115273' => [ // Mer de Chine méridionale
                'Chine', 'Viêt Nam', 'Philippines', 'Malaisie', 'Brunei',
                'Indonésie', 'Singapour', 'Cambodge', 'Thaïlande',
            ],
            '1159115305' => [ // Mer du Japon
                'Japon', 'Corée du Sud', 'Corée du Nord', 'Russie', 'Chine',
            ],
            '1159115321' => [ // Mer Méditerranée
                'France', 'Espagne', 'Italie', 'Grèce', 'Turquie', 'Liban', 'Syrie',
                'Israël', 'Égypte', 'Libye', 'Tunisie', 'Algérie', 'Maroc',
                'Slovénie', 'Croatie', 'Bosnie-Herzégovine', 'Monténégro', 'Albanie',
                'Malte', 'Chypre', 'Monaco',
            ],
            '1159115343' => [ // Mer d'Arabie
                'Inde', 'Pakistan', 'Iran', 'Oman', 'Yémen', 'Djibouti', 'Somalie',
            ],
            '1159115359' => [ // Mer de Beaufort
                'Canada', 'États-Unis',
            ],
            '1159115379' => [ // Mer des Caraïbes
                'Cuba', 'Haïti', 'République dominicaine', 'Jamaïque',
                'Trinité-et-Tobago', 'Barbade', 'Grenade', 'Saint-Vincent-et-les-Grenadines',
                'Sainte-Lucie', 'Dominique', 'Saint-Kitts-et-Nevis', 'Antigua-et-Barbuda',
                'Venezuela', 'Colombie', 'Panama', 'Costa Rica', 'Nicaragua',
                'Honduras', 'Belize', 'Mexique', 'États-Unis',
            ],
            '1159115399' => [ // Golfe du Mexique
                'Mexique', 'États-Unis', 'Cuba',
            ],
            '1159115419' => [ // Mer du Labrador
                'Canada', 'Danemark',
            ],
            '1159115441' => [ // Baie d'Hudson
                'Canada',
            ],
            '1159115461' => [ // Mer Caspienne
                'Kazakhstan', 'Russie', 'Azerbaïdjan', 'Iran', 'Turkménistan',
            ],
            '1159115483' => [ // Mer de Baffin
                'Canada', 'Danemark',
            ],
            '1159115503' => [ // Golfe d'Alaska
                'États-Unis', 'Canada',
            ],
            '1159115521' => [ // Mer Rouge
                'Égypte', 'Érythrée', 'Djibouti', 'Arabie saoudite', 'Yémen',
                'Jordanie', 'Israël', 'Soudan',
            ],
            '1159115541' => [ // Mer d'Okhotsk
                'Russie', 'Japon',
            ],
            '1159115579' => [// Mer de Weddell — eaux antarctiques
            ],
            '1159115597' => [ // Golfe Persique
                'Bahreïn', 'Émirats arabes unis', 'Koweït', 'Qatar',
                'Arabie saoudite', 'Iran', 'Irak', 'Oman',
            ],
            '1159115615' => [// Mer de Ross — eaux antarctiques
            ],
        ];

        $areas = MarineArea::all()->keyBy('geojson_id');

        foreach ($coastalMap as $geojsonId => $countryNames) {
            $area = $areas->get($geojsonId);
            if (! $area || empty($countryNames)) {
                continue;
            }

            $countryIds = Country::whereIn('name', $countryNames)->pluck('id');
            $area->coastalCountries()->sync($countryIds);
        }
    }
}
