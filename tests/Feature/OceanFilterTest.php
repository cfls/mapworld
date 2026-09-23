<?php

use App\Models\MarineArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('ocean filter shows all group pills', function () {
    Livewire::test('ocean-filter')
        ->assertSee('Tous')
        ->assertSee('Pacifique')
        ->assertSee('Atlantique')
        ->assertSee('Indien')
        ->assertSee('Arctique')
        ->assertSee('Austral');
});

test('ocean filter selects a group and dispatches event', function () {
    Livewire::test('ocean-filter')
        ->call('selectGroup', 'atlantique')
        ->assertSet('selectedGroup', 'atlantique')
        ->assertDispatched('ocean-group-selected', group: 'atlantique');
});

test('ocean filter resets to tous and dispatches null', function () {
    Livewire::test('ocean-filter')
        ->call('selectGroup', 'pacifique')
        ->call('selectGroup', null)
        ->assertSet('selectedGroup', null)
        ->assertDispatched('ocean-group-selected', group: null);
});

test('ocean filter syncs group when marine-area-selected is dispatched', function () {
    $area = MarineArea::factory()->create(['ocean_group' => 'indien']);

    Livewire::test('ocean-filter')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSet('selectedGroup', 'indien');
});
