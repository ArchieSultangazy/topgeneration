<?php

namespace App\Models\CL;

use App\Models\CL\Rating\RateCourse;
use App\Models\CL\Rating\RateLesson;
use App\Models\UserResults;
use App\User;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\VarDumper\Dumper\esc;

/**
 * @SWG\Definition(
 *  definition="Course",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="locale", type="string"),
 *  @SWG\Property(property="slug", type="string"),
 *  @SWG\Property(property="title", type="string"),
 *  @SWG\Property(property="img_preview", type="string"),
 *  @SWG\Property(property="video", type="string"),
 *  @SWG\Property(property="body_in", type="string"),
 *  @SWG\Property(property="body_out", type="string"),
 *  @SWG\Property(property="duration", type="integer"),
 *  @SWG\Property(property="rating", type="integer"),
 *  @SWG\Property(property="is_published", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Course extends Model
{
    use Sluggable, SoftDeletes;

    protected $table = 'cl_courses';

    protected $fillable = [
        'locale',
        'slug',
        'title',
        'img_preview',
        'video',
        'body_in',
        'body_out',
        'duration',
        'rating',
        'is_published',
    ];

    protected $appends = [
        'img_src',
        'video_src',
        'lessons_amount',
        'lesson_duration',
        'user_rate',
        'lessons_rating',
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    const STATUS_NOT_PUBLISHED = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_DEVELOPING = 2;

    public function themes()
    {
        return $this->belongsToMany(Theme::class, 'cl_course_themes');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'cl_course_authors');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function comments()
    {
        return $this->hasMany(CourseComment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_cl_courses');
    }

    public function ratedUsers()
    {
        return $this->hasMany(RateCourse::class);
    }

    //Appended Attributes
    public function getLessonsAmountAttribute()
    {
        return $this->lessons()->count();
    }

    public function getLessonsListAttribute()
    {
        return $this->lessons()->get();
    }

    public function getAuthorsListAttribute()
    {
        return $this->authors()->get();
    }

    public function getCommentsListAttribute()
    {
        return $this->comments()->whereNull('parent_id')->get();
    }

    public function getImgSrcAttribute()
    {
        $img = null;

        if (!is_null($this->img_preview)) {
            $img = env('APP_URL') . config('filesystems.disks.cl_course.url') . $this->img_preview;
        }

        return $img;
    }

    public function getVideoSrcAttribute()
    {
        $video = null;

        if (!is_null($this->video)) {
            $video = env('APP_URL') . config('filesystems.disks.cl_course.url') . $this->video;
        }

        return $video;
    }

    public function getUserCourseAttribute()
    {
        $userCourse = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $userCourse = UserCourse::where('course_id', $this->id)->where('user_id', $user->id)->first();
        }

        return $userCourse;
    }

    public function getUsersFinishedAmountAttribute()
    {
        return $this->users()->where('finished', 1)->count();
    }

    public function getUsersFinishedPercentAttribute()
    {
        $users_finished = $this->users()->where('finished', 1)->count();
        $users_proceeded = $users_finished + count($this->users_started_ids);

        if ($users_finished + $users_proceeded <= 0) {
            return 0;
        }
        return number_format(100 * $users_finished/($users_finished + $users_proceeded),2);
    }

    public function getUsersStartedIdsAttribute()
    {
        $users = $this->users()
            ->where('finished', 0)
            ->orderBy('created_at', 'DESC')
            ->get()->pluck('id');

        return $users;
    }

    public function getUserRateAttribute()
    {
        $rate = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $ratedCourse = $user->ratedCourses()->where('course_id', $this->id)->first();
            $rate = !is_null($ratedCourse) ? $ratedCourse->value : null;
        }

        return $rate;
    }

    public function getLessonsRatingAttribute()
    {
        if ($this->lessons()->count() <= 0) {
            return null;
        }
        
        $rating = $this->lessons()->sum('rating')/$this->lessons()->count();

        return round($rating, 2);
    }

    public function getLastUserLessonAttribute()
    {
        if (Auth::guard('api')->guest()) {
            return null;
        }

        $lessons = Lesson::query()->where('course_id', $this->id)
            ->orderBy('position')
            ->get()
            ->toArray();

        $resp = ['id' => $lessons[0]['id'] ?? null, 'type' => 'lesson'];
        $lastLesson = null;
        foreach ($lessons as $key => $lesson) {
            if ($lesson['user_access']) {
                $lastLesson = $lesson;
            } else {
                break;
            }
        }

        if ($lastLesson['user_finished'] == 0) {
            $type = 'lesson';
        } else {
            if (is_null($lastLesson['test'])) {
                $type = 'lesson';
            } else {
                $type = 'quiz';
            }
        }

        $resp['id'] = $lastLesson['id'];
        $resp['type'] = $type;

        return $resp;
    }

    public function getLessonDurationAttribute()
    {
        return $this->lessons()->sum('duration');
    }

    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_NOT_PUBLISHED => 'Не опубликованный',
            self::STATUS_PUBLISHED => 'Опубликованный',
            self::STATUS_DEVELOPING => 'В разработке',
        ];
    }

    public function getThemesAttribute()
    {
        return $this->themes()->get();
    }
}
