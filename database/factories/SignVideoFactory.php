<?php

namespace Database\Factories;

use App\Enums\SignVideoType;
use App\Models\Country;
use App\Models\SignVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SignVideo>
 */
class SignVideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'type' => $this->faker->randomElement(SignVideoType::cases())->value,
            'cloudinary_public_id' => 'countryworld/'.$this->faker->uuid(),
            'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/v1/'.$this->faker->uuid().'.mp4',
            'thumbnail_url' => null,
            'duration_seconds' => $this->faker->optional()->numberBetween(10, 300),
        ];
    }

    public function lsfb(): static
    {
        return $this->state(['type' => SignVideoType::Lsfb->value]);
    }

    public function international(): static
    {
        return $this->state(['type' => SignVideoType::International->value]);
    }
}
