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

test('country detail shows no-video message when country has no videos', function () {
    $country = Country::factory()->create();

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('Aucune vidéo disponible pour ce pays');
});

test('country detail renders lsfb video player when video exists', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->forCountry($country)->create([
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/dog.mp4',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/dog.mp4');
});

test('country detail renders international video player when video exists', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->international()->forCountry($country)->create([
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/intl.mp4',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/intl.mp4');
});

test('country detail resets to empty state after continent-selected', function () {
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);
    SignVideo::factory()->lsfb()->forCountry($country)->create([
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/dog.mp4',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/dog.mp4')
        ->dispatch('continent-selected', continentId: $continent->id)
        ->assertDontSee('https://res.cloudinary.com/demo/video/upload/dog.mp4');
});

test('country detail updates when a different country is selected', function () {
    $continent = Continent::factory()->create();
    $belgium = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique']);
    $france = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'France']);
    SignVideo::factory()->lsfb()->forCountry($belgium)->create([
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/belgium.mp4',
    ]);
    SignVideo::factory()->lsfb()->forCountry($france)->create([
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/france.mp4',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $belgium->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/belgium.mp4')
        ->dispatch('country-selected', countryId: $france->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/france.mp4')
        ->assertDontSee('https://res.cloudinary.com/demo/video/upload/belgium.mp4');
});

test('country detail resets to empty state on map-reset', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->forCountry($country)->create([
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/dog.mp4',
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/dog.mp4')
        ->dispatch('map-reset')
        ->assertSee('Sélectionnez un pays');
});
