<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountrySignLanguage extends Model
{
    protected $fillable = ['country_id', 'name', 'year_official'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
