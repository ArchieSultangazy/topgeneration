<?php

namespace App\Models\CL;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TestQuestion
 * @package App\Models\CL
 *
 * @property integer $id
 * @property integer $lesson_test_id
 * @property string $ru_name
 * @property string $kk_name
 * @property string $en_name
 * @property integer $correct_answer_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class TestQuestion extends Model
{
    protected $table = 'cl_lesson_tests_questions';

    protected $fillable = [
        'lesson_test_id',
        'ru_name',
        'kk_name',
        'en_name',
    ];

    public function test()
    {
        return $this->hasOne(Test::class, 'id', 'lesson_test_id');
    }

    public function answers()
    {
        return $this->hasMany(TestAnswer::class, 'question_id', 'id');
    }
}
