<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountrySignLanguage extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'sigle',
        'year_official',
    ];

    protected $casts = [
        'year_official' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
