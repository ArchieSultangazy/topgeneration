<?php

namespace App\Models\CL;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="CourseComment",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="course_id", type="integer"),
 *  @SWG\Property(property="body", type="string"),
 *  @SWG\Property(property="rating", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class CourseComment extends Model
{
    use SoftDeletes;

    protected $table = 'cl_course_comments';

    protected $fillable = [
        'parent_id',
        'user_id',
        'course_id',
        'body',
        'rating',
    ];

    protected $appends = [
        'user_rate',
        'children',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function ratedUsers()
    {
        return $this->hasMany(RateCourseComment::class, 'comment_id', 'id');
    }

    public function getUserRateAttribute()
    {
        $rate = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $ratedComment = $user->ratedCourseComments()->where('comment_id', $this->id)->first();
            $rate = !is_null($ratedComment) ? $ratedComment->value : null;
        }

        return $rate;
    }

    public function getChildrenAttribute()
    {
        return CourseComment::query()->where('parent_id', $this->id)->get();
    }
}
