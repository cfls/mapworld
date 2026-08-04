<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin countries route requires authentication', function () {
    $this->get('/admin/countries')->assertRedirect('/admin/login');
});

test('country manager shows list of countries', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create(['name' => 'Europe']);
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique']);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->assertSee('Belgique')
        ->assertSee('Europe');
});

test('country manager can create a new country', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startCreate')
        ->set('name', 'Luxembourg')
        ->set('isoCode', 'LUX')
        ->set('continentId', $continent->id)
        ->call('save')
        ->assertSee('créé avec succès');

    expect(Country::where('iso_code', 'LUX')->exists())->toBeTrue();
});

test('country manager validates required fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startCreate')
        ->call('save')
        ->assertHasErrors(['name', 'isoCode', 'continentId']);
});

test('country manager validates iso code uniqueness', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'iso_code' => 'LUX']);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startCreate')
        ->set('name', 'Luxembro')
        ->set('isoCode', 'LUX')
        ->set('continentId', $continent->id)
        ->call('save')
        ->assertHasErrors('isoCode');
});

test('country manager can edit an existing country', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startEdit', $country->id)
        ->set('name', 'Belgique modifiée')
        ->call('save')
        ->assertSee('mis à jour avec succès');

    expect($country->fresh()->name)->toBe('Belgique modifiée');
});

test('country manager can delete a country', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('confirmDelete', $country->id)
        ->call('delete')
        ->assertSee('supprimé');

    expect(Country::find($country->id))->toBeNull();
});

test('country manager can search countries by name', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);
    Country::factory()->create(['continent_id' => $continent->id, 'name' => 'France', 'iso_code' => 'FRA']);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->set('search', 'Belg')
        ->assertSee('Belgique')
        ->assertDontSee('France');
});

test('country manager can filter by continent', function () {
    $user = User::factory()->create();
    $europe = Continent::factory()->create(['name' => 'Europe']);
    $africa = Continent::factory()->create(['name' => 'Afrique']);
    Country::factory()->create(['continent_id' => $europe->id, 'name' => 'Belgique', 'iso_code' => 'BEL']);
    Country::factory()->create(['continent_id' => $africa->id, 'name' => 'Maroc', 'iso_code' => 'MAR']);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->set('filterContinentId', $europe->id)
        ->assertSee('Belgique')
        ->assertDontSee('Maroc');
});
