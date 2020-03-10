<?php

namespace App\Models\CL;

use App\Models\UserResults;
use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="UserLesson",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="lesson_id", type="integer"),
 *  @SWG\Property(property="finished", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class UserLesson extends Model
{
    protected $table = 'user_cl_lessons';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'finished',
    ];

    public function getLastTestProcessAttribute() {
        $last_process = UserResults::query()->where('user_id', $this->user_id)
            ->where('lesson_id', $this->lesson_id)
            ->orderBy('updated_at', 'DESC')
            ->first()
            ->updated_at ?? null;

        return $last_process;
    }
}
