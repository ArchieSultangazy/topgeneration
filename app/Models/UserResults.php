<?php

namespace App\Models;

use App\Models\CL\TestQuestion;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserResults
 * @package App\Models
 *
 * @property integer $user_id
 * @property integer $test_id
 * @property integer $lesson_id
 * @property float $result
 * @property bool $success
 */
class UserResults extends Model
{
    protected $table = 'user_results';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scopeFinished(Builder $q)
    {
        return $q->whereNotNull('finished_at');
    }

    public function scopeNotFinished(Builder $q)
    {
        return $q->whereNull('finished_at');
    }

    public function getWrongQuestionsModelsAttribute()
    {
        $ids = json_decode($this->wrong_questions);

        return TestQuestion::query()->whereIn('id', $ids ?? [])->get();
    }
}
