<?php

namespace App\Models;

use App\Enums\SignVideoType;
use Database\Factories\SignVideoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignVideo extends Model
{
    /** @use HasFactory<SignVideoFactory> */
    use HasFactory;

    protected $fillable = [
        'country_id',
        'type',
        'cloudinary_public_id',
        'cloudinary_url',
        'thumbnail_url',
        'duration_seconds',
    ];

    protected $casts = [
        'type' => SignVideoType::class,
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
