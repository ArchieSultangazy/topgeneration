<?php

namespace App\Models\QA;

use App\Models\QA\Rating\RateQuestion;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="Question",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="title", type="string"),
 *  @SWG\Property(property="body", type="string"),
 *  @SWG\Property(property="rating", type="integer"),
 *  @SWG\Property(property="views", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 *  @SWG\Property(property="has_correct_answer", type="boolean"),
 * )
 */
class Question extends Model
{
    use SoftDeletes;

    protected $table = 'qa_questions';

    protected $fillable = [
        'locale',
        'user_id',
        'title',
        'body',
        'rating',
        'views',
    ];

    protected $appends = [
        'has_correct_answer',
        'user_rate',
        'user_favorite',
    ];

    //Relations
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id', 'id');
    }

    public function ratedUsers()
    {
        return $this->hasMany(RateQuestion::class);
    }

    public function favUsers()
    {
        return $this->belongsToMany(User::class, 'user_fav_questions');
    }

    public function themes()
    {
        return $this->belongsToMany(Theme::class, 'qa_theme_questions');
    }

    //Attributes
    public function getAuthorAttribute()
    {
        return $this->author()->first();
    }

    public function getAnswersAttribute()
	{
		return $this->answers()->get();
	}

    public function getThemesAttribute()
    {
        return $this->themes()->get();
    }

	public function getFavCountAttribute()
    {
        return $this->favUsers()->count();
    }

    public function getAnswersCountAttribute()
    {
        return $this->answers()->count();
    }

    public function getHasCorrectAnswerAttribute()
    {
        $isCorrectValues = $this->answers()->pluck('is_correct')->toArray();

        return in_array(1, $isCorrectValues);
    }

    public function getUserRateAttribute()
    {
        $rate = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $ratedQuestion = $user->ratedQuestions()->where('question_id', $this->id)->first();
            $rate = !is_null($ratedQuestion) ? $ratedQuestion->value : null;
        }

        return $rate;
    }

    public function getUserFavoriteAttribute()
    {
        $favorite = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $favorite = $user->favQuestions->contains($this->id);
        }

        return $favorite;
    }
}
