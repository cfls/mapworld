<?php

use App\Models\Continent;
use App\Models\Country;
use Database\Seeders\ContinentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('continent has correct fillable attributes', function () {
    $continent = Continent::factory()->create(['name' => 'Europe', 'slug' => 'europe']);

    expect($continent->name)->toBe('Europe')
        ->and($continent->slug)->toBe('europe');
});

test('continent has many countries', function () {
    $continent = Continent::factory()->create();
    Country::factory()->count(3)->create(['continent_id' => $continent->id]);

    expect($continent->countries)->toHaveCount(3);
});

test('seeder creates exactly five continents in french', function () {
    $this->seed(ContinentSeeder::class);

    $names = Continent::pluck('name')->sort()->values()->all();

    expect($names)->toBe(['Afrique', 'Amerique', 'Asie', 'Europe', 'Oceanie']);
});

test('continent slugs are unique', function () {
    $this->seed(ContinentSeeder::class);

    $slugs = Continent::pluck('slug');

    expect($slugs->unique())->toHaveCount($slugs->count());
});
