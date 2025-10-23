<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $table = 'teachers';
    protected $fillable = [
        'name',
        'birth_date',
        'email',
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
        'google_search'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function curriculums(): HasMany
    {
        return $this->hasMany(Curriculum::class, 'teacher_id');
    }

    public function socialMedias(): HasMany
    {
        return $this->hasMany(SocialMedia::class, 'teacher_id');
    }

}
