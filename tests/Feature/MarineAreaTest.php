<?php

use App\Enums\SignVideoType;
use App\Models\MarineArea;
use App\Models\SignVideo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('marine area has name, slug, geojson_id and type', function () {
    $area = MarineArea::factory()->ocean()->create([
        'name' => 'océan Atlantique',
        'slug' => 'ocean-atlantique',
        'geojson_id' => '1159115057',
    ]);

    expect($area->name)->toBe('océan Atlantique')
        ->and($area->slug)->toBe('ocean-atlantique')
        ->and($area->geojson_id)->toBe('1159115057')
        ->and($area->type)->toBe('ocean');
});

test('marine area slug is unique', function () {
    MarineArea::factory()->create(['slug' => 'mer-du-nord']);

    expect(fn () => MarineArea::factory()->create(['slug' => 'mer-du-nord']))
        ->toThrow(QueryException::class);
});

test('marine area type defaults to ocean', function () {
    $area = MarineArea::factory()->ocean()->create();

    expect($area->type)->toBe('ocean');
});

test('marine area factory sea state sets correct type', function () {
    $area = MarineArea::factory()->sea()->create();

    expect($area->type)->toBe('sea');
});

test('marine area has lsfb sign video via polymorphic relation', function () {
    $area = MarineArea::factory()->create();
    SignVideo::factory()->lsfb()->create([
        'signable_type' => MarineArea::class,
        'signable_id' => $area->id,
    ]);

    expect($area->lsfbVideos()->count())->toBe(1)
        ->and($area->lsfbVideos->first()->type)->toBe(SignVideoType::Lsfb);
});

test('marine area has international sign video via polymorphic relation', function () {
    $area = MarineArea::factory()->create();
    SignVideo::factory()->international()->create([
        'signable_type' => MarineArea::class,
        'signable_id' => $area->id,
    ]);

    expect($area->internationalVideo->type)->toBe(SignVideoType::International);
});

test('marine area lsfb videos relation returns only lsfb type', function () {
    $area = MarineArea::factory()->create();
    SignVideo::factory()->lsfb()->create(['signable_type' => MarineArea::class, 'signable_id' => $area->id]);
    SignVideo::factory()->international()->create(['signable_type' => MarineArea::class, 'signable_id' => $area->id]);

    expect($area->lsfbVideos()->count())->toBe(1)
        ->and($area->lsfbVideo->type)->toBe(SignVideoType::Lsfb);
});

test('marine area can have multiple lsfb videos', function () {
    $area = MarineArea::factory()->create();
    SignVideo::factory()->lsfb()->create([
        'signable_type' => MarineArea::class,
        'signable_id' => $area->id,
        'cloudinary_public_id' => 'mers/atlantique_B_aaa111',
    ]);
    SignVideo::factory()->lsfb()->create([
        'signable_type' => MarineArea::class,
        'signable_id' => $area->id,
        'cloudinary_public_id' => 'mers/atlantique_1_B_bbb222',
    ]);

    expect($area->lsfbVideos()->count())->toBe(2);
});

test('sign video signable relation resolves to marine area', function () {
    $area = MarineArea::factory()->create();
    $video = SignVideo::factory()->lsfb()->create([
        'signable_type' => MarineArea::class,
        'signable_id' => $area->id,
    ]);

    expect($video->signable)->toBeInstanceOf(MarineArea::class)
        ->and($video->signable->id)->toBe($area->id);
});
