<?php

namespace App\Models\CL;

use Illuminate\Database\Eloquent\Model;

class UserCourse extends Model
{
    protected $table = 'user_cl_courses';

    protected $fillable = [
        'user_id',
        'course_id',
        'progress',
        'finished',
        'finished_at',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
