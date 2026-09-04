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
        'currency_code',
        'population_year',
        'entity_type',
        'parent_country',
    ];

    protected $casts = [
        'languages' => 'array',
    ];

    protected function currencyLabel(): Attribute
    {
        return Attribute::get(function () {
            if ($this->currency === null) {
                return null;
            }

            if ($this->currency_code === null) {
                return $this->currency;
            }

            return "{$this->currency_code} — {$this->currency}";
        });
    }

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
