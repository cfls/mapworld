<?php

namespace App\Models;

use App\Enums\SignVideoType;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    protected $fillable = [
        'continent_id',
        'name',
        'iso_code',
        'flag_path',
        'latitude',
        'longitude',
        'slug',
    ];

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function signVideos(): HasMany
    {
        return $this->hasMany(SignVideo::class);
    }

    public function lsfbVideo(): HasOne
    {
        return $this->hasOne(SignVideo::class)->where('type', SignVideoType::Lsfb->value);
    }

    public function internationalVideo(): HasOne
    {
        return $this->hasOne(SignVideo::class)->where('type', SignVideoType::International->value);
    }
}
