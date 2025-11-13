<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassDate extends Model
{
    protected $table = 'class_dates';

    protected $fillable = [
        'subject_id',
        'sort_order',
        'flag',
        'date',
        ];

        public function classModel(): HasOne
        {
        return $this->hasOne(ClassModel::class);
        }

}
