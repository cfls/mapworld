<?php

use App\Enums\SignVideoType;
use App\Models\Country;
use App\Models\SignVideo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sign video belongs to a country', function () {
    $country = Country::factory()->create();
    $video = SignVideo::factory()->create(['country_id' => $country->id]);

    expect($video->country->id)->toBe($country->id);
});

test('sign video type is cast to enum', function () {
    $video = SignVideo::factory()->lsfb()->create();

    expect($video->type)->toBe(SignVideoType::Lsfb);
});

test('sign video type enum has lsfb and international cases', function () {
    expect(SignVideoType::cases())->toHaveCount(2)
        ->and(SignVideoType::Lsfb->value)->toBe('lsfb')
        ->and(SignVideoType::International->value)->toBe('international');
});

test('each country cannot have two videos of the same type', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->create(['country_id' => $country->id]);

    expect(fn () => SignVideo::factory()->lsfb()->create(['country_id' => $country->id]))
        ->toThrow(QueryException::class);
});

test('country can have one lsfb and one international video', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->create(['country_id' => $country->id]);
    SignVideo::factory()->international()->create(['country_id' => $country->id]);

    expect($country->signVideos()->count())->toBe(2);
});
