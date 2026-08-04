<?php

use App\Enums\SignVideoType;
use App\Models\Continent;
use App\Models\Country;
use App\Models\SignVideo;
use Database\Seeders\ContinentSeeder;
use Database\Seeders\CountrySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('country belongs to a continent', function () {
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);

    expect($country->continent->id)->toBe($continent->id);
});

test('country iso_code is unique', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'iso_code' => 'FRA']);

    expect(fn () => Country::factory()->create(['continent_id' => $continent->id, 'iso_code' => 'FRA']))
        ->toThrow(QueryException::class);
});

test('country lsfb video relation returns only lsfb type', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->create(['country_id' => $country->id]);
    SignVideo::factory()->international()->create(['country_id' => $country->id]);

    expect($country->lsfbVideo->type)->toBe(SignVideoType::Lsfb)
        ->and($country->internationalVideo->type)->toBe(SignVideoType::International);
});

test('seeder creates 196 countries across five continents', function () {
    $this->seed([ContinentSeeder::class, CountrySeeder::class]);

    expect(Country::count())->toBe(196);
});

test('seeder countries have valid iso codes of length 3', function () {
    $this->seed([ContinentSeeder::class, CountrySeeder::class]);

    $invalid = Country::whereRaw('LENGTH(iso_code) != 3')->count();

    expect($invalid)->toBe(0);
});

test('all country iso codes are unique', function () {
    $this->seed([ContinentSeeder::class, CountrySeeder::class]);

    $total = Country::count();
    $unique = Country::distinct()->count('iso_code');

    expect($unique)->toBe($total);
});
