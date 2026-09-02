<?php

namespace Database\Seeders;

use App\Models\MarineArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarineAreaSeeder extends Seeder
{
    public function run(): void
    {
        $geojsonPath = public_path('geojson/world-oceans.json');

        if (! file_exists($geojsonPath)) {
            $this->command->warn('world-oceans.json not found — skipping MarineAreaSeeder.');

            return;
        }

        $features = json_decode(file_get_contents($geojsonPath), true)['features'];

        // Maps ne_id → French name override for features with duplicate name_fr
        $labelOverrides = [
            1159115057 => 'océan Atlantique Nord',
            1159115149 => 'océan Atlantique Sud',
            1159115079 => 'océan Pacifique Nord',
            1159115099 => 'océan Pacifique Sud',
        ];

        $typeMap = [
            'ocean' => 'ocean',
            'sea' => 'sea',
            'gulf' => 'gulf',
            'bay' => 'bay',
        ];

        $slugsSeen = [];

        foreach ($features as $feature) {
            $props = $feature['properties'];
            $neId = (int) $props['ne_id'];

            $name = $labelOverrides[$neId]
                ?? $props['name_fr']
                ?? $props['name'];

            $type = $typeMap[$props['featurecla']] ?? 'other';

            $slug = Str::slug($name);
            if (in_array($slug, $slugsSeen, strict: true)) {
                $slug .= '-'.$neId;
            }
            $slugsSeen[] = $slug;

            MarineArea::create([
                'name' => $name,
                'slug' => $slug,
                'geojson_id' => (string) $neId,
                'type' => $type,
            ]);
        }
    }
}
