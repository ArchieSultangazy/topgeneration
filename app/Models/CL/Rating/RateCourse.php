<?php

namespace App\Models\CL\Rating;

use Illuminate\Database\Eloquent\Model;

class RateCourse extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'value',
    ];

    public $timestamps = false;
}
