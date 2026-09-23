<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\SignVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// --- welcome page loads ---

test('welcome page loads successfully', function () {
    $this->get('/')->assertStatus(200);
});

test('welcome page renders pays section', function () {
    $this->get('/')->assertSee('panel-pays');
});

test('welcome page renders mers section', function () {
    $this->get('/')->assertSee('panel-mers');
});

// --- pays section still works ---

test('country list shows pays section with continent filters', function () {
    $continent = Continent::factory()->create(['name' => 'Europe', 'slug' => 'europe']);
    $country = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique']);
    SignVideo::factory()->lsfb()->forCountry($country)->create();

    Livewire::test('continent-filter')
        ->assertSee('Europe');

    Livewire::test('country-list')
        ->set('search', 'Belg')
        ->assertSee('Belgique');
});

test('country section still renders without errors after mers changes', function () {
    Livewire::test('world-map')->assertOk();
    Livewire::test('country-detail')->assertOk();
    Livewire::test('country-info')->assertOk();
    Livewire::test('continent-filter')->assertOk();
    Livewire::test('country-list')->assertOk();
});

// --- mers section renders ---

test('ocean section components render without errors', function () {
    Livewire::test('ocean-filter')->assertOk();
    Livewire::test('marine-list')->assertOk();
    Livewire::test('ocean-map')->assertOk();
    Livewire::test('ocean-detail')->assertOk();
    Livewire::test('marine-info')->assertOk();
});
