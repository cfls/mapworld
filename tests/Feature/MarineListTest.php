<?php

use App\Models\MarineArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('marine list shows placeholder when search is empty', function () {
    Livewire::test('marine-list')
        ->assertSee('Sélectionnez une zone sur la carte ou recherchez ici');
});

test('marine list search filters by name', function () {
    MarineArea::factory()->create(['name' => 'mer Méditerranée', 'slug' => 'mer-mediterranee']);

    Livewire::test('marine-list')
        ->set('search', 'Médit')
        ->assertSee('mer Méditerranée');
});

test('marine list search shows areas even without videos', function () {
    MarineArea::factory()->create(['name' => 'mer sans vidéo', 'slug' => 'mer-sans-video']);

    Livewire::test('marine-list')
        ->set('search', 'mer sans')
        ->assertSee('mer sans vidéo');
});

test('marine list filters by ocean group', function () {
    MarineArea::factory()->create(['name' => 'mer Pacifique', 'slug' => 'mer-pacifique', 'ocean_group' => 'pacifique']);
    MarineArea::factory()->create(['name' => 'mer Atlantique', 'slug' => 'mer-atlantique', 'ocean_group' => 'atlantique']);

    Livewire::test('marine-list')
        ->dispatch('ocean-group-selected', group: 'pacifique')
        ->set('search', 'mer ')
        ->assertSee('mer Pacifique')
        ->assertDontSee('mer Atlantique');
});

test('marine list select area dispatches marine-area-selected', function () {
    $area = MarineArea::factory()->create(['name' => 'océan Indien', 'slug' => 'ocean-indien']);

    Livewire::test('marine-list')
        ->call('selectArea', $area->id)
        ->assertSet('selectedAreaId', $area->id)
        ->assertSet('selectedAreaSlug', 'ocean-indien')
        ->assertDispatched('marine-area-selected', marineAreaId: $area->id);
});

test('marine list syncs selection when marine-area-selected dispatched', function () {
    $area = MarineArea::factory()->create(['name' => 'mer Noire', 'slug' => 'mer-noire']);

    Livewire::test('marine-list')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSet('selectedAreaId', $area->id)
        ->assertSet('selectedAreaName', 'mer Noire');
});

test('marine list dispatches marine-area-selected on mount when zone in url', function () {
    $area = MarineArea::factory()->create(['name' => 'océan Arctique', 'slug' => 'ocean-arctique']);

    Livewire::withQueryParams(['zone' => 'ocean-arctique'])
        ->test('marine-list')
        ->assertDispatched('marine-area-selected', marineAreaId: $area->id);
});
