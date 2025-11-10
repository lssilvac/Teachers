<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'weekday',
        'start_date',
        'end_date',
        'user_id',
        'school_id',
        'class_type_id',
    ];

    protected $casts = [
        'weekday' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',

    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classType(): BelongsTo
    {
        return $this->belongsTo(ClassType::class);
    }

    public function schoolYears(): HasMany
    {
        return $this->hasMany(SchoolYear::class, 'class_id');
    }

}
