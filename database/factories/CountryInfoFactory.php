<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\CountryInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CountryInfo>
 */
class CountryInfoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'capital' => $this->faker->city(),
            'languages' => [$this->faker->languageCode()],
            'population' => $this->faker->numberBetween(50_000, 1_500_000_000),
            'currency' => $this->faker->currencyCode(),
            'population_year' => $this->faker->numberBetween(2020, 2025),
        ];
    }
}
