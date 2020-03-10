<?php

namespace App\Models\CL;

use Illuminate\Database\Eloquent\Model;

class RateCourseComment extends Model
{
    protected $table = 'rate_course_comments';

    protected $fillable = [
        'user_id',
        'comment_id',
        'value',
    ];

    public $timestamps = false;
}
