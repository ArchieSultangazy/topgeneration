<?php

namespace App\Models\CL;

use Illuminate\Database\Eloquent\Model;

class TestAnswer extends Model
{
    protected $table = 'cl_lesson_tests_answers';

    protected $fillable = [
        'question_id',
        'ru_name',
        'kk_name',
        'en_name',
        'is_correct',
    ];

    public function question()
    {
        return $this->hasOne(TestQuestion::class, 'id', 'question_id');
    }
}
