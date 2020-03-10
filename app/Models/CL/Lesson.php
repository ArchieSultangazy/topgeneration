<?php

namespace App\Models\CL;

use App\Models\CL\Rating\RateLesson;
use App\Models\UserResults;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="Lesson",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="position", type="integer"),
 *  @SWG\Property(property="course_id", type="integer"),
 *  @SWG\Property(property="title", type="string"),
 *  @SWG\Property(property="img_preview", type="string"),
 *  @SWG\Property(property="video", type="string"),
 *  @SWG\Property(property="body", type="string"),
 *  @SWG\Property(property="scheme", type="string"),
 *  @SWG\Property(property="files", type="string"),
 *  @SWG\Property(property="articles", type="string"),
 *  @SWG\Property(property="rating", type="integer"),
 *  @SWG\Property(property="duration", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Lesson extends Model
{
    use SoftDeletes;

    protected $table = 'cl_lessons';

    protected $fillable = [
        'position',
        'course_id',
        'title',
        'img_preview',
        'video',
        'body',
        'body_short',
        'scheme',
        'files',
        'articles',
        'rating',
        'duration',
    ];

    protected $appends = [
        'img_src',
        'video_src',
        'course_title',
        'user_finished',
        'user_access',
        'user_rate',
        'test',
        'test_result',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function files()
    {
        return $this->hasMany(LessonFile::class);
    }

    public function comments()
    {
        return $this->hasMany(LessonComment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_cl_lessons');
    }

    public function ratedUsers()
    {
        return $this->hasMany(RateLesson::class);
    }

    //Appended attributes
    public function getFilesListAttribute()
    {
        return $this->files()->get();
    }

    public function getCommentsListAttribute()
    {
        return $this->comments()->whereNull('parent_id')->get();
    }

    public function getImgSrcAttribute()
    {
        $img = null;

        if (!is_null($this->img_preview)) {
            $img = env('APP_URL') . config('filesystems.disks.cl_lesson.url') . $this->img_preview;
        }

        return $img;
    }

    public function getVideoSrcAttribute()
    {
        $video = null;

        if (!is_null($this->video)) {
            if (strpos($this->video, 'http') !== false) {
                $video = $this->video;
            } else {
                $video = env('APP_URL') . config('filesystems.disks.cl_lesson.url') . $this->video;
            }
        }

        return $video;
    }

    public function getCourseTitleAttribute()
    {
        return Course::find($this->course_id)->title;
    }

    public function getUserFinishedAttribute()
    {
        $userLesson = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $userLesson = UserLesson::where('lesson_id', $this->id)->where('user_id', $user->id)->first();
        }

        return $userLesson->finished ?? null;
    }

    public function getUserRateAttribute()
    {
        $rate = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $ratedLesson = $user->ratedLessons()->where('lesson_id', $this->id)->first();
            $rate = !is_null($ratedLesson) ? $ratedLesson->value : null;
        }

        return $rate;
    }

    public function getUserAccessAttribute()
    {
        if (Auth::guard('api')->guest()) {
            return false;
        }

        $access = false;
        $course = Course::find($this->course_id);
        $lessons = $course->lessons()->orderBy('position')->get();

        if (!is_null($this->position)) {
            $previous = Lesson::where('course_id', $this->course_id)->where('position', '<', $this->position)->orderBy('position','desc')->first();
        } else {
            $previous = Lesson::where('course_id', $this->course_id)->where('id', '<', $this->id)->orderBy('id','desc')->first();
        }
        $userPrevious = UserLesson::where('user_id', Auth::guard('api')->id())->where('lesson_id', $previous->id ?? null)->first();

        if ($this->id == $lessons->first()->id) {
            $access = true;
        } else if (isset($userPrevious)) {
            if ($userPrevious->finished == 0) {
                $access = false;
            } else if (is_null(Test::where('lesson_id', $previous->id)->first())) {
                $access = true;
            } else {
                $userResult = UserResults::where('user_id', Auth::guard('api')->id())
                    ->where('test_id', Test::where('lesson_id', $previous->id)->first()->id)
                    ->where('success', 1)
                    ->first();

                if (is_null($userResult)) {
                    $access = false;
                } else {
                    $access = true;
                }
            }
        }

        return $access;
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'lesson_id', 'id');
    }

    public function results()
    {
        return $this->hasMany(UserResults::class, 'lesson_id', 'id');
    }

    public function getTestAttribute()
    {
        return $this->tests()->first();
    }

    public function getTestResultAttribute()
    {
        return $this->results()->where('user_id', Auth::guard('api')->id())
            ->orderBy('created_at', 'DESC')->first();
    }
}
