<?php

namespace App\Models\CL\Rating;

use Illuminate\Database\Eloquent\Model;

class RateLesson extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'value',
    ];

    public $timestamps = false;
}
