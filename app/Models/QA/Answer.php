<?php

namespace App\Models\QA;

use App\Models\QA\Rating\RateAnswer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="Answer",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="question_id", type="integer"),
 *  @SWG\Property(property="is_correct", type="integer"),
 *  @SWG\Property(property="body", type="string"),
 *  @SWG\Property(property="rating", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Answer extends Model
{
    use SoftDeletes;

    protected $table = 'qa_answers';

    protected $fillable = [
        'user_id',
        'question_id',
        'is_correct',
        'body',
        'rating',
    ];

    protected $appends = [
        'comments',
        'user_rate',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'answer_id', 'id');
    }

    public function ratedUsers()
    {
        return $this->hasMany(RateAnswer::class);
    }

    public function getCommentsAttribute()
	{
		return $this->comments()->whereNull('parent_id')->get();
	}

    public function getUserRateAttribute()
    {
        $rate = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $ratedAnswer = $user->ratedAnswers()->where('answer_id', $this->id)->first();
            $rate = !is_null($ratedAnswer) ? $ratedAnswer->value : null;
        }

        return $rate;
    }
}
