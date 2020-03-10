<?php

namespace App\Models\CL;

use App\Models\QA\Question;
use App\Models\UserResults;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Test
 * @package App\Models\CL
 *
 * @property integer $id
 * @property integer $lesson_id
 * @property integer $created_user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Test extends Model
{
    protected $table = 'cl_lesson_tests';

    public function questions()
    {
        return $this->hasMany(TestQuestion::class, 'lesson_test_id', 'id');
    }

    public function userResults()
    {
        return $this->hasMany(UserResults::class, 'test_id', 'id');
    }
}
