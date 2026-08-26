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

test('country iso2 is unique', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'iso2' => 'BE']);

    expect(fn () => Country::factory()->create(['continent_id' => $continent->id, 'iso2' => 'BE']))
        ->toThrow(QueryException::class);
});

test('country iso3 is unique', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'iso3' => 'BEL']);

    expect(fn () => Country::factory()->create(['continent_id' => $continent->id, 'iso3' => 'BEL']))
        ->toThrow(QueryException::class);
});

test('country lsfb video relation returns only lsfb type', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->create(['country_id' => $country->id]);
    SignVideo::factory()->international()->create(['country_id' => $country->id]);

    expect($country->lsfbVideo->type)->toBe(SignVideoType::Lsfb)
        ->and($country->internationalVideo->type)->toBe(SignVideoType::International);
});

test('flag accessor generates emoji from iso2', function () {
    $country = Country::factory()->make(['iso2' => 'BE', 'flag_path' => null]);

    expect($country->flag)->toBe('🇧🇪');
});

test('flag accessor returns null when iso2 is null', function () {
    $country = Country::factory()->make(['iso2' => null, 'flag_path' => null]);

    expect($country->flag)->toBeNull();
});

test('flag accessor does not throw when both iso2 and flag_path are null', function () {
    $country = Country::factory()->make(['iso2' => null, 'flag_path' => null]);

    expect(fn () => $country->flag)->not->toThrow(Throwable::class);
});

test('seeder creates 196 countries across five continents', function () {
    $this->seed([ContinentSeeder::class, CountrySeeder::class]);

    expect(Country::count())->toBe(196);
});

test('seeder countries have valid iso3 codes of length 3', function () {
    $this->seed([ContinentSeeder::class, CountrySeeder::class]);

    $invalid = Country::whereNotNull('iso3')->whereRaw('LENGTH(iso3) != 3')->count();

    expect($invalid)->toBe(0);
});

test('all country iso3 codes are unique', function () {
    $this->seed([ContinentSeeder::class, CountrySeeder::class]);

    $total = Country::whereNotNull('iso3')->count();
    $unique = Country::whereNotNull('iso3')->distinct()->count('iso3');

    expect($unique)->toBe($total);
});

test('country does not use iso_code field', function () {
    expect(Country::factory()->make()->toArray())->not->toHaveKey('iso_code');
});
