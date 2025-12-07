<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model

{
    protected $fillable = [
        'class_id',
        'invite_id',
        'dates',
        'sort_order'
    ];

    protected $casts = [
        'dates' => 'array',
    ];

    Public function teachers() : HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function accepted(): BelongsTo
    {
        return $this->belongsTo(Invite::class, 'invite_id');
    }

    public function invites(): BelongsToMany
    {
        return $this->belongsToMany(Invite::class, 'invite_dates');
    }
}
