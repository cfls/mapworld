<?php

namespace App\Models;

use App\Enums\SignVideoType;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    protected $fillable = [
        'continent_id',
        'name',
        'iso2',
        'iso3',
        'flag_path',
        'latitude',
        'longitude',
        'slug',
        'video_lsfb',
        'video_int',
    ];

    protected function flag(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->iso2) {
                return null;
            }

            return collect(str_split(strtoupper($this->iso2)))
                ->map(fn ($letter) => mb_chr(ord($letter) - ord('A') + 0x1F1E6))
                ->implode('');
        });
    }

    public function info(): HasOne
    {
        return $this->hasOne(CountryInfo::class);
    }

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function signVideos(): MorphMany
    {
        return $this->morphMany(SignVideo::class, 'signable');
    }

    public function lsfbVideo(): MorphOne
    {
        return $this->morphOne(SignVideo::class, 'signable')
            ->where('type', SignVideoType::Lsfb->value);
    }

    public function lsfbVideos(): MorphMany
    {
        return $this->morphMany(SignVideo::class, 'signable')
            ->where('type', SignVideoType::Lsfb->value)
            ->orderBy('cloudinary_public_id');
    }

    public function internationalVideo(): MorphOne
    {
        return $this->morphOne(SignVideo::class, 'signable')
            ->where('type', SignVideoType::International->value);
    }
}
