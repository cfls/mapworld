<?php

use App\Enums\SignVideoType;
use App\Models\Country;
use App\Models\SignVideo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sign video belongs to a signable entity', function () {
    $country = Country::factory()->create();
    $video = SignVideo::factory()->forCountry($country)->create();

    expect($video->signable->id)->toBe($country->id)
        ->and($video->signable)->toBeInstanceOf(Country::class);
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

test('each sign video must have a unique cloudinary_public_id', function () {
    SignVideo::factory()->lsfb()->create(['cloudinary_public_id' => 'pays/Belgique_B_abc123']);

    expect(fn () => SignVideo::factory()->lsfb()->create(['cloudinary_public_id' => 'pays/Belgique_B_abc123']))
        ->toThrow(QueryException::class);
});

test('a country can have multiple lsfb videos with different cloudinary ids', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->forCountry($country)->create(['cloudinary_public_id' => 'pays/Belgique_B_aaa111']);
    SignVideo::factory()->lsfb()->forCountry($country)->create(['cloudinary_public_id' => 'pays/Belgique_1_B_bbb222']);

    expect($country->lsfbVideos()->count())->toBe(2);
});

test('country can have one lsfb and one international video', function () {
    $country = Country::factory()->create();
    SignVideo::factory()->lsfb()->forCountry($country)->create();
    SignVideo::factory()->international()->forCountry($country)->create();

    expect($country->signVideos()->count())->toBe(2);
});
