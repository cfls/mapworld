<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\CountryInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// --- country-detail display ---

test('country detail shows info section when country has info', function () {
    $continent = Continent::factory()->create(['name' => 'Europe', 'slug' => 'europe']);
    $country = Country::factory()->create(['continent_id' => $continent->id, 'name' => 'Belgique']);
    CountryInfo::factory()->create([
        'country_id' => $country->id,
        'capital' => 'Bruxelles',
        'languages' => ['Français', 'Néerlandais'],
        'population' => 11600000,
        'currency' => 'Euro (EUR)',
        'population_year' => 2024,
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('Bruxelles')
        ->assertSee('Français')
        ->assertSee('Néerlandais')
        ->assertSee('Euro (EUR)')
        ->assertSee('2024');
});

test('country detail formats population with french thousands separator', function () {
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);
    CountryInfo::factory()->create([
        'country_id' => $country->id,
        'population' => 11600000,
    ]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('11'."\u{00A0}".'600'."\u{00A0}".'000');
});

test('country detail shows no info section when country has no info', function () {
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertDontSee('Informations');
});

test('country detail shows lsfb fallback when no video uploaded', function () {
    $country = Country::factory()->create();

    Livewire::test('country-detail')
        ->dispatch('country-selected', countryId: $country->id)
        ->assertSee('Vidéo LSFB pas encore disponible');
});

// --- CountryInfo model ---

test('country info formatted population uses non-breaking space as thousands separator', function () {
    $info = new CountryInfo(['population' => 11600000]);

    expect($info->formatted_population)->toBe('11'."\u{00A0}".'600'."\u{00A0}".'000');
});

test('country info formatted population returns null when population is null', function () {
    $info = new CountryInfo(['population' => null]);

    expect($info->formatted_population)->toBeNull();
});

test('country info languages cast to array', function () {
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);
    $info = CountryInfo::factory()->create([
        'country_id' => $country->id,
        'languages' => ['Français', 'Néerlandais'],
    ]);

    expect($info->fresh()->languages)->toBeArray()->toBe(['Français', 'Néerlandais']);
});

// --- admin country-manager ---

test('country manager saves info when creating a country', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startCreate')
        ->set('name', 'Belgique')
        ->set('continentId', $continent->id)
        ->set('capital', 'Bruxelles')
        ->set('languagesRaw', 'Français, Néerlandais')
        ->set('population', '11600000')
        ->set('currency', 'Euro (EUR)')
        ->set('populationYear', '2024')
        ->call('save')
        ->assertSee('créé avec succès');

    $country = Country::where('name', 'Belgique')->firstOrFail();
    expect($country->info)->not->toBeNull()
        ->and($country->info->capital)->toBe('Bruxelles')
        ->and($country->info->languages)->toBe(['Français', 'Néerlandais'])
        ->and($country->info->population)->toBe(11600000)
        ->and($country->info->currency)->toBe('Euro (EUR)')
        ->and($country->info->population_year)->toBe(2024);
});

test('country manager loads existing info when editing a country', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);
    CountryInfo::factory()->create([
        'country_id' => $country->id,
        'capital' => 'TestCapital',
        'currency' => 'Euro (EUR)',
    ]);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startEdit', $country->id)
        ->assertSet('capital', 'TestCapital')
        ->assertSet('currency', 'Euro (EUR)');
});

test('country manager updates existing info when editing a country', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);
    CountryInfo::factory()->create([
        'country_id' => $country->id,
        'capital' => 'OldCapital',
    ]);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startEdit', $country->id)
        ->set('capital', 'NewCapital')
        ->call('save')
        ->assertSee('mis à jour avec succès');

    expect($country->fresh()->info->capital)->toBe('NewCapital');
});

test('country manager deletes info when all info fields cleared on edit', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();
    $country = Country::factory()->create(['continent_id' => $continent->id]);
    CountryInfo::factory()->create([
        'country_id' => $country->id,
        'capital' => 'Bruxelles',
        'languages' => null,
        'population' => null,
        'currency' => null,
        'population_year' => null,
    ]);

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startEdit', $country->id)
        ->set('capital', '')
        ->call('save');

    expect($country->fresh()->info)->toBeNull();
});

test('country manager does not create info when no info fields are set', function () {
    $user = User::factory()->create();
    $continent = Continent::factory()->create();

    Livewire::actingAs($user)
        ->test('admin.country-manager')
        ->call('startCreate')
        ->set('name', 'Luxembourg')
        ->set('continentId', $continent->id)
        ->call('save');

    $country = Country::where('name', 'Luxembourg')->firstOrFail();
    expect($country->info)->toBeNull();
});
