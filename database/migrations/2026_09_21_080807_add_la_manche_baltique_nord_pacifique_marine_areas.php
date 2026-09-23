<?php

use App\Models\Country;
use App\Models\MarineArea;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $atlantiqueNord = MarineArea::where('geojson_id', '1159115057')->first();

        $areas = [
            [
                'name' => 'La Manche',
                'type' => 'sea',
                'ocean_group' => 'atlantique',
                'parent_id' => $atlantiqueNord?->id,
                'surface_km2' => 75_000,
                'max_depth_m' => 172,
                'countries' => ['France', 'Royaume-Uni'],
            ],
            [
                'name' => 'mer Baltique',
                'type' => 'sea',
                'ocean_group' => 'atlantique',
                'parent_id' => $atlantiqueNord?->id,
                'surface_km2' => 415_000,
                'max_depth_m' => 459,
                'countries' => [
                    'Allemagne', 'Danemark', 'Suède', 'Finlande',
                    'Estonie', 'Lettonie', 'Lituanie', 'Pologne', 'Russie',
                ],
            ],
            [
                'name' => 'mer du Nord',
                'type' => 'sea',
                'ocean_group' => 'atlantique',
                'parent_id' => $atlantiqueNord?->id,
                'surface_km2' => 570_000,
                'max_depth_m' => 700,
                'countries' => [
                    'Royaume-Uni', 'Norvège', 'Danemark', 'Allemagne',
                    'Pays-Bas', 'Belgique', 'France',
                ],
            ],
            [
                'name' => 'océan Pacifique',
                'type' => 'ocean',
                'ocean_group' => 'pacifique',
                'parent_id' => null,
                'surface_km2' => 165_250_000,
                'max_depth_m' => 10_911,
                'countries' => [
                    'Russie', 'Japon', 'Chine', 'Corée du Nord', 'Corée du Sud',
                    'Philippines', 'Canada', 'États-Unis', 'Mexique',
                    'Australie', 'Nouvelle-Zélande', 'Fidji', 'Papouasie-Nouvelle-Guinée',
                    'Salomon', 'Vanuatu', 'Tonga', 'Samoa', 'Kiribati', 'Tuvalu', 'Nauru',
                    'Chili', 'Pérou', 'Équateur', 'Colombie', 'Panama', 'Costa Rica',
                    'Indonésie', 'Malaisie', 'Viêt Nam', 'Thaïlande', 'Cambodge', 'Myanmar',
                ],
            ],
        ];

        foreach ($areas as $data) {
            $countries = $data['countries'];
            unset($data['countries']);

            $area = MarineArea::create([
                'slug' => Str::slug($data['name']),
                ...$data,
            ]);

            $countryIds = Country::whereIn('name', $countries)->pluck('id');
            $area->coastalCountries()->sync($countryIds);
        }
    }

    public function down(): void
    {
        MarineArea::whereIn('slug', [
            'la-manche',
            'mer-baltique',
            'mer-du-nord',
            'ocean-pacifique',
        ])->each(function (MarineArea $area) {
            $area->coastalCountries()->detach();
            $area->delete();
        });
    }
};
