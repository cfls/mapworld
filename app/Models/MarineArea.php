<?php

namespace App\Models;

use App\Enums\SignVideoType;
use Database\Factories\MarineAreaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class MarineArea extends Model
{
    /** @use HasFactory<MarineAreaFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'geojson_id',
        'type',
        'ocean_group',
        'surface_km2',
        'max_depth_m',
        'status',
    ];

    protected $casts = [
        'surface_km2' => 'integer',
        'max_depth_m' => 'integer',
        'status' => 'boolean',
    ];

    /** @param  Builder<MarineArea>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', true);
    }

    public function parentArea(): BelongsTo
    {
        return $this->belongsTo(MarineArea::class, 'parent_id');
    }

    public function childAreas(): HasMany
    {
        return $this->hasMany(MarineArea::class, 'parent_id');
    }

    public function coastalCountries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'marine_area_country');
    }

    public function signVideos(): MorphMany
    {
        return $this->morphMany(SignVideo::class, 'signable');
    }

    public function lsfbVideos(): MorphMany
    {
        return $this->morphMany(SignVideo::class, 'signable')
            ->where('type', SignVideoType::Lsfb->value)
            ->orderBy('cloudinary_public_id');
    }

    public function lsfbVideo(): MorphOne
    {
        return $this->morphOne(SignVideo::class, 'signable')
            ->where('type', SignVideoType::Lsfb->value);
    }

    public function internationalVideo(): MorphOne
    {
        return $this->morphOne(SignVideo::class, 'signable')
            ->where('type', SignVideoType::International->value);
    }

    protected function formattedSurface(): Attribute
    {
        return Attribute::get(function () {
            if ($this->surface_km2 === null) {
                return null;
            }

            return number_format($this->surface_km2, 0, ',', "\u{00A0}");
        });
    }

    protected function oceanGroupLabel(): Attribute
    {
        return Attribute::get(function () {
            return match ($this->ocean_group) {
                'pacifique' => 'Pacifique',
                'atlantique' => 'Atlantique',
                'indien' => 'Indien',
                'arctique' => 'Arctique',
                'austral' => 'Austral',
                default => null,
            };
        });
    }
}
