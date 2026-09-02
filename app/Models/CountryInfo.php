<?php

namespace App\Models;

use Database\Factories\CountryInfoFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryInfo extends Model
{
    /** @use HasFactory<CountryInfoFactory> */
    use HasFactory;

    protected $fillable = [
        'country_id',
        'capital',
        'languages',
        'population',
        'currency',
        'population_year',
    ];

    protected $casts = [
        'languages' => 'array',
    ];

    protected function formattedPopulation(): Attribute
    {
        return Attribute::get(function () {
            if ($this->population === null) {
                return null;
            }

            return number_format($this->population, 0, ',', "\u{00A0}");
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
