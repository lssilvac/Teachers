<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InviteDate extends Model
{

    protected $fillable = [
        'invite_id',
        'school_year_id'
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function invite(): BelongsTo
    {
        return $this->belongsTo(Invite::class);
    }


}
