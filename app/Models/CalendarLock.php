<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarLock extends Model
{
    protected $fillable = [
        'invite_id',
        'date'
    ];

    public function invite(): BelongsTo
    {
        return $this->belongsTo(Invite::class);
    }
}
