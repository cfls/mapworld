<?php

use App\Models\MarineArea;
use App\Models\SignVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('ocean detail shows empty state when no area is selected', function () {
    Livewire::test('ocean-detail')
        ->assertSee('Sélectionnez une zone marine');
});

test('ocean detail shows area name after marine-area-selected event', function () {
    $area = MarineArea::factory()->ocean()->create(['name' => 'océan Atlantique Nord']);

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('océan Atlantique Nord');
});

test('ocean detail shows type label océan for ocean type', function () {
    $area = MarineArea::factory()->ocean()->create();

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('Océan');
});

test('ocean detail shows type label mer for sea type', function () {
    $area = MarineArea::factory()->sea()->create();

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('Mer');
});

test('ocean detail shows lsfb fallback when no video uploaded', function () {
    $area = MarineArea::factory()->create();

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('Vidéo LSFB pas encore disponible');
});

test('ocean detail shows international fallback when no video uploaded', function () {
    $area = MarineArea::factory()->create();

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('Vidéo en Signe International pas encore disponible');
});

test('ocean detail renders lsfb video player when video exists', function () {
    $area = MarineArea::factory()->create();
    SignVideo::factory()->lsfb()->create([
        'signable_type' => MarineArea::class,
        'signable_id' => $area->id,
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/ocean_lsfb.mp4',
    ]);

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/ocean_lsfb.mp4');
});

test('ocean detail renders international video player when video exists', function () {
    $area = MarineArea::factory()->create();
    SignVideo::factory()->international()->create([
        'signable_type' => MarineArea::class,
        'signable_id' => $area->id,
        'cloudinary_url' => 'https://res.cloudinary.com/demo/video/upload/ocean_intl.mp4',
    ]);

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('https://res.cloudinary.com/demo/video/upload/ocean_intl.mp4');
});

test('ocean detail updates when a different area is selected', function () {
    $atlantique = MarineArea::factory()->ocean()->create(['name' => 'océan Atlantique']);
    $pacifique = MarineArea::factory()->ocean()->create(['name' => 'océan Pacifique']);

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $atlantique->id)
        ->assertSee('océan Atlantique')
        ->dispatch('marine-area-selected', marineAreaId: $pacifique->id)
        ->assertSee('océan Pacifique')
        ->assertDontSee('océan Atlantique');
});

test('ocean detail shows golfe label for gulf type', function () {
    $area = MarineArea::factory()->create(['type' => 'gulf']);

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('Golfe');
});

test('ocean detail shows baie label for bay type', function () {
    $area = MarineArea::factory()->create(['type' => 'bay']);

    Livewire::test('ocean-detail')
        ->dispatch('marine-area-selected', marineAreaId: $area->id)
        ->assertSee('Baie');
});
