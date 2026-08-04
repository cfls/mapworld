<?php

use App\Models\Continent;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('country list shows all countries by default', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'France', 'iso_code' => 'FRA']);

    Livewire::test('country-list')
        ->assertSee('Belgique')
        ->assertSee('France');
});

test('country list search filters by name', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'France', 'iso_code' => 'FRA']);

    Livewire::test('country-list')
        ->set('search', 'Belg')
        ->assertSee('Belgique')
        ->assertDontSee('France');
});

test('country list search shows empty state when no match', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);

    Livewire::test('country-list')
        ->set('search', 'zzzzz')
        ->assertSee('Aucun pays trouvé');
});

test('country list search resets when continent filter changes', function () {
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);

    Livewire::test('country-list')
        ->set('search', 'Belg')
        ->dispatch('continent-selected', continentId: $continent->id)
        ->assertSet('search', '');
});

test('country list search and continent filter combine', function () {
    $europe = Continent::factory()->create(['name' => 'Europe']);
    $africa = Continent::factory()->create(['name' => 'Afrique']);
    Country::factory()->create(['continent_id' => $europe->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);
    Country::factory()->create(['continent_id' => $europe->id, 'name' => 'France', 'iso_code' => 'FRA']);
    Country::factory()->create(['continent_id' => $africa->id, 'name' => 'Maroc', 'iso_code' => 'MAR']);

    Livewire::test('country-list')
        ->dispatch('continent-selected', continentId: $europe->id)
        ->set('search', 'Fra')
        ->assertSee('France')
        ->assertDontSee('Belgique')
        ->assertDontSee('Maroc');
});
