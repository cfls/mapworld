<?php

namespace Database\Factories;

use App\Models\Continent;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->country();

        return [
            'continent_id' => Continent::factory(),
            'name' => $name,
            'iso2' => strtoupper($this->faker->unique()->lexify('??')),
            'iso3' => strtoupper($this->faker->unique()->lexify('???')),
            'flag_path' => null,
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'slug' => Str::slug($name),
            'video_lsfb' => null,
            'video_int' => null,
        ];
    }
}
