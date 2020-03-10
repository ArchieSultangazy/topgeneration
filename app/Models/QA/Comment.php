<?php

namespace App\Models\QA;

use App\Models\QA\Rating\RateComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="Comment",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="answer_id", type="integer"),
 *  @SWG\Property(property="body", type="string"),
 *  @SWG\Property(property="rating", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Comment extends Model
{
    use SoftDeletes;

    protected $table = 'qa_comments';

    protected $fillable = [
        'parent_id',
        'user_id',
        'answer_id',
        'body',
        'rating',
    ];

    protected $appends = [
        'user_rate',
        'children',
    ];

    public function answer()
    {
        return $this->belongsTo(Answer::class, 'answer_id', 'id');
    }

    public function ratedUsers()
    {
        return $this->hasMany(RateComment::class);
    }

    public function getUserRateAttribute()
    {
        $rate = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $ratedComment = $user->ratedComments()->where('comment_id', $this->id)->first();
            $rate = !is_null($ratedComment) ? $ratedComment->value : null;
        }

        return $rate;
    }

    public function getChildrenAttribute()
    {
        return Comment::query()->where('parent_id', $this->id)->get();
    }
}
