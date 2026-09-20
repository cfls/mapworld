<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\MarineArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('marine info shows nothing when no area is selected', function () {
    Livewire::test('marine-info')
        ->assertDontSee('INFO');
});

test('marine info shows type and group when area is selected', function () {
    $area = MarineArea::factory()->create([
        'name' => 'mer Méditerranée',
        'type' => 'sea',
        'ocean_group' => 'atlantique',
        'surface_km2' => 2510000,
        'max_depth_m' => 5267,
    ]);

    Livewire::test('marine-info')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('INFO')
        ->assertSee('Mer')
        ->assertSee('Atlantique');
});

test('marine info shows formatted surface and depth', function () {
    $area = MarineArea::factory()->create([
        'surface_km2' => 2510000,
        'max_depth_m' => 5267,
    ]);

    Livewire::test('marine-info')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('2'."\u{00A0}".'510'."\u{00A0}".'000')
        ->assertSee('5'."\u{00A0}".'267');
});

test('marine info shows parent area name', function () {
    $parent = MarineArea::factory()->create(['name' => 'océan Atlantique Nord']);
    $child = MarineArea::factory()->create([
        'name' => 'mer des Caraïbes',
        'parent_id' => $parent->id,
    ]);

    Livewire::test('marine-info')
        ->dispatch('marine-area-selected', marineAreaId: $child->id)
        ->assertSee('océan Atlantique Nord');
});

test('marine info shows child areas', function () {
    $parent = MarineArea::factory()->create(['name' => 'mer Méditerranée']);
    MarineArea::factory()->create(['name' => 'mer Noire', 'parent_id' => $parent->id]);

    Livewire::test('marine-info')
        ->dispatch('marine-area-selected', marineAreaId: $parent->id)
        ->assertSee('mer Noire');
});

test('marine info shows coastal countries', function () {
    $continent = Continent::factory()->create();
    $france = Country::factory()->create(['name' => 'France', 'continent_id' => $continent->id]);
    $area = MarineArea::factory()->create(['name' => 'mer Méditerranée']);
    $area->coastalCountries()->attach($france);

    Livewire::test('marine-info')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('France');
});
