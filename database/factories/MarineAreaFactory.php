<?php

namespace Database\Factories;

use App\Models\MarineArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MarineArea>
 */
class MarineAreaFactory extends Factory
{
    public function definition(): array
    {
        $name = 'mer '.$this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'geojson_id' => (string) $this->faker->unique()->numerify('##########'),
            'type' => $this->faker->randomElement(['ocean', 'sea', 'gulf', 'bay']),
        ];
    }

    public function ocean(): static
    {
        return $this->state(['type' => 'ocean']);
    }

    public function sea(): static
    {
        return $this->state(['type' => 'sea']);
    }
}
