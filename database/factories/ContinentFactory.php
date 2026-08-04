<?php

namespace Database\Factories;

use App\Models\Continent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Continent>
 */
class ContinentFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Afrique', 'Amerique', 'Asie', 'Europe', 'Oceanie']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
