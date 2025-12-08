<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model

{
    use HasFactory;
    protected $fillable = [
        'name',
        'street_number',
        'route',
        'sublocality_level_1',
        'locality',
        'administrative_area_level_1',
        'administrative_area_level_2',
        'country',
        'postal_code',
        'place_id',
        'formatted_address',
        'latitude',
        'longitude',
        'google_search',
    ];



    public function classes(): HasMany
    {
        return $this -> hasMany(ClassModel::class);
    }

}
