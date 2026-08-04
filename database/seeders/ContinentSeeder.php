<?php

namespace Database\Seeders;

use App\Models\Continent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContinentSeeder extends Seeder
{
    public function run(): void
    {
        $continents = ['Afrique', 'Amerique', 'Asie', 'Europe', 'Oceanie'];

        foreach ($continents as $name) {
            Continent::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
