<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\SignVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('country detail shows empty state when no country is selected', function () {
    Livewire::test('country-detail')
        ->assertSee('Sélectionnez un pays');
});

test('country detail shows country name and iso code after event', function () {
    $continent = Continent::factory()->create(['name' => 'Europe', 'slug' => 'europe']);
    $country = Country::factory()->create([
        'continent_id' => $continent->id,
        'name' => 'Belgique',
        'iso3' => 'BEL',
        'iso2' => 'BE',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('Belgique')
        ->assertSee('BEL')
        ->assertSee('Europe');
});

test('country detail shows lsfb fallback when no video uploaded', function () {
    $country = Country::factory()->create();

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('Vidéo LSFB pas encore disponible');
});

test('country detail shows international fallback when no video uploaded', function () {
    $country = Country::factory()->create();

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('Vidéo en Signes Internationaux pas encore disponible');
});

test('country detail renders lsfb video player when video exists', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->create([
        'country_id' => $country->id,
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/dog.mp4',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/dog.mp4');
});

test('country detail renders international video player when video exists', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->international()->create([
        'country_id' => $country->id,
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/intl.mp4',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/intl.mp4');
});

test('country detail updates when a different country is selected', function () {
    $continent = Continent::factory()->create();
    $belgium = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso3' => 'BEL', 'iso2' => 'BE']);
    $france = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'France', 'iso3' => 'FRA', 'iso2' => 'FR']);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $belgium->id)
        ->assertSee('Belgique')
        ->dispatch('country-selected', countryId: $france->id)
        ->assertSee('France')
        ->assertDontSee('Belgique');
});
