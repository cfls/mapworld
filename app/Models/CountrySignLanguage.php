<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountrySignLanguage extends Model
{
    protected $fillable = [
        'country_id',
        'nom',
        'sigle',
        'annee_de_reconnaissance',
    ];

    protected $casts = [
        'annee_de_reconnaissance' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
