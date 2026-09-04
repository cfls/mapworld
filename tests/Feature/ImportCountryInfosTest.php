<?php

use App\Models\Country;
use App\Models\CountryInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.restcountries.url', 'https://api.restcountries.com');
    config()->set('services.restcountries.key', 'test-key');
});

function fakeCountryPayload(array $overrides = []): array
{
    return array_merge([
        'names' => ['translations' => ['fra' => ['common' => 'Costa Rica']]],
        'capitals' => [['name' => 'San José']],
        'currencies' => [['code' => 'CRC', 'name' => 'Costa Rican colón', 'symbol' => '₡']],
        'languages' => [[
            'bcp47' => 'es',
            'iso639_1' => 'es',
            'name' => 'Spanish',
            'native_name' => 'español',
        ]],
        'population' => 5160700,
    ], $overrides);
}

function fakeApiResponse(array $object): array
{
    return ['data' => ['objects' => [$object]]];
}

test('command creates country_info from API response', function () {
    Http::fake([
        '*/countries/v5/codes.alpha_3/CRI*' => Http::response(fakeApiResponse(fakeCountryPayload())),
    ]);

    $country = Country::factory()->create(['iso3' => 'CRI', 'name' => 'Costa Rica']);

    $this->artisan('countries:import-infos', ['--iso3' => ['CRI']])
        ->assertSuccessful();

    $info = CountryInfo::where('country_id', $country->id)->firstOrFail();

    expect($info->capital)->toBe('San José')
        ->and($info->currency)->toBe('colón costaricain')
        ->and($info->population)->toBe(5160700)
        ->and($info->population_year)->toBeNull()
        ->and($info->languages)->toBe([[
            'code' => 'es',
            'name' => 'Espagnol',
            'native_name' => 'español',
        ]]);
});

test('command skips countries with existing info unless --force is set', function () {
    Http::fake([
        '*/countries/v5/codes.alpha_3/CRI*' => Http::response(fakeApiResponse(fakeCountryPayload([
            'capitals' => [['name' => 'Nouvelle Capitale']],
        ]))),
    ]);

    $country = Country::factory()->create(['iso3' => 'CRI']);
    CountryInfo::factory()->create([
        'country_id' => $country->id,
        'capital' => 'Ancienne Capitale',
    ]);

    $this->artisan('countries:import-infos', ['--iso3' => ['CRI']])->assertSuccessful();

    expect($country->fresh()->info->capital)->toBe('Ancienne Capitale');
    Http::assertNothingSent();

    $this->artisan('countries:import-infos', ['--iso3' => ['CRI'], '--force' => true])->assertSuccessful();

    expect($country->fresh()->info->capital)->toBe('Nouvelle Capitale');
});

test('command skips countries without iso3', function () {
    Http::fake();

    Country::factory()->create(['iso3' => null]);

    $this->artisan('countries:import-infos')->assertSuccessful();

    expect(CountryInfo::count())->toBe(0);
    Http::assertNothingSent();
});

test('dry run does not persist changes', function () {
    Http::fake([
        '*/countries/v5/codes.alpha_3/CRI*' => Http::response(fakeApiResponse(fakeCountryPayload())),
    ]);

    Country::factory()->create(['iso3' => 'CRI']);

    $this->artisan('countries:import-infos', ['--iso3' => ['CRI'], '--dry-run' => true])
        ->assertSuccessful();

    expect(CountryInfo::count())->toBe(0);
});

test('command continues when a country request fails', function () {
    Http::fake([
        '*/countries/v5/codes.alpha_3/AAA*' => Http::response('boom', 500),
        '*/countries/v5/codes.alpha_3/CRI*' => Http::response(fakeApiResponse(fakeCountryPayload())),
    ]);

    Country::factory()->create(['iso3' => 'AAA', 'name' => 'Aaa']);
    $ok = Country::factory()->create(['iso3' => 'CRI', 'name' => 'Costa Rica']);

    $this->artisan('countries:import-infos', ['--iso3' => ['AAA', 'CRI']])
        ->assertSuccessful();

    expect(CountryInfo::where('country_id', $ok->id)->exists())->toBeTrue()
        ->and(CountryInfo::count())->toBe(1);
});
