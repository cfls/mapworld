<?php

namespace App\Models;

use App\Enums\SignVideoType;
use Database\Factories\MarineAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class MarineArea extends Model
{
    /** @use HasFactory<MarineAreaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'geojson_id',
        'type',
    ];

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
}
