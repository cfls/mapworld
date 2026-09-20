<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\SignVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('country list shows placeholder when no search term', function () {
    Livewire::test('country-list')
        ->assertSee('Sélectionnez un pays sur la carte ou recherchez ici');
});

test('country list search filters by name', function () {
    $continent = Continent::factory()->create();
    $belgium = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso3' => 'BEL']);
    $france = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'France', 'iso3' => 'FRA']);
    SignVideo::factory()->lsfb()->forCountry($belgium)->create();
    SignVideo::factory()->lsfb()->forCountry($france)->create();

    Livewire::test('country-list')
        ->set('search', 'Belg')
        ->assertSee('Belgique')
        ->assertDontSee('France');
});

test('country list search shows empty state when no match', function () {
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso3' => 'BEL']);
    SignVideo::factory()->lsfb()->forCountry($country)->create();

    Livewire::test('country-list')
        ->set('search', 'zzzzz')
        ->assertSee('Aucun pays trouvé');
});

test('country list search resets when continent filter changes', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso3' => 'BEL']);

    Livewire::test('country-list')
        ->set('search', 'Belg')
        ->dispatch('continent-selected', continentId: $continent->id)
        ->assertSet('search', '');
});

test('country list search and continent filter combine', function () {
    $europe = Continent::factory()->create(['name' => 'Europe']);
    $africa = Continent::factory()->create(['name' => 'Afrique']);
    $belgium = Country::factory()->create(['continent_id' => $europe->id, 'name' => 'Belgique', 'iso3' => 'BEL']);
    $france = Country::factory()->create(['continent_id' => $europe->id, 'name' => 'France', 'iso3' => 'FRA']);
    $maroc = Country::factory()->create(['continent_id' => $africa->id, 'name' => 'Maroc', 'iso3' => 'MAR']);
    SignVideo::factory()->lsfb()->forCountry($belgium)->create();
    SignVideo::factory()->lsfb()->forCountry($france)->create();
    SignVideo::factory()->lsfb()->forCountry($maroc)->create();

    Livewire::test('country-list')
        ->dispatch('continent-selected', continentId: $europe->id)
        ->set('search', 'Fra')
        ->assertSee('France')
        ->assertDontSee('Belgique')
        ->assertDontSee('Maroc');
});

test('country list only shows countries with videos', function () {
    $continent = Continent::factory()->create();
    $withVideo = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso3' => 'BEL']);
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'France', 'iso3' => 'FRA']);
    SignVideo::factory()->lsfb()->forCountry($withVideo)->create();

    Livewire::test('country-list')
        ->set('search', 'Fra')
        ->assertDontSee('France');
});
