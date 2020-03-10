<?php

namespace App\Models\CL;

use Illuminate\Database\Eloquent\Model;

class RateLessonComment extends Model
{
    protected $table = 'rate_lesson_comments';

    protected $fillable = [
        'user_id',
        'comment_id',
        'value',
    ];

    public $timestamps = false;
}
