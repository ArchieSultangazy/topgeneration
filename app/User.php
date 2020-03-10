<?php

namespace App;

use App\Models\AccessGroup;
use App\Models\CL\Course;
use App\Models\CL\Lesson;
use App\Models\CL\RateCourseComment;
use App\Models\CL\RateLessonComment;
use App\Models\CL\Rating\RateCourse;
use App\Models\CL\Rating\RateLesson;
use App\Models\Job\Specialization;
use App\Models\Job\UserJob;
use App\Models\KB\Article;
use App\Models\KB\Rating\RateArticle;
use App\Models\KB\Comment as KBComment;
use App\Models\Location\District;
use App\Models\Location\Locality;
use App\Models\Location\Region;
use App\Models\QA\Answer;
use App\Models\QA\Comment as QAComment;
use App\Models\QA\Question;
use App\Models\QA\Rating\RateAnswer;
use App\Models\QA\Rating\RateComment as QARateComment;
use App\Models\QA\Rating\RateQuestion;
use App\Models\QA\Theme as QATheme;
use App\Models\KB\Theme as KBTheme;
use App\Models\KB\Rating\RateComment as KBRateComment;
use App\Models\School;
use App\Models\UserAchievement;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Laravel\Passport\HasApiTokens;

/**
 * @SWG\Definition(
 *  definition="User",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="username", type="string"),
 *  @SWG\Property(property="phone", type="string"),
 *  @SWG\Property(property="phone_verified_at", type="string"),
 *  @SWG\Property(property="firstname", type="string"),
 *  @SWG\Property(property="lastname", type="string"),
 *  @SWG\Property(property="middlename", type="string"),
 *  @SWG\Property(property="email", type="string"),
 *  @SWG\Property(property="avatar", type="string"),
 *  @SWG\Property(property="avatar_url", type="string"),
 *  @SWG\Property(property="status", type="string"),
 *  @SWG\Property(property="about", type="string"),
 *  @SWG\Property(property="site", type="string"),
 *  @SWG\Property(property="contacts", type="string"),
 *  @SWG\Property(property="birth_date", type="string"),
 *  @SWG\Property(property="region_id", type="integer"),
 *  @SWG\Property(property="last_seen", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */

/**
 * Class User
 * @package App
 *
 * @property-read HasMany achievements
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    const TYPE_ADMIN = 1;
    const TYPE_TEACHER = 2;

    const TYPE_GUEST = 10;
    const TYPE_ENTREPRENEUR = 11;
    const TYPE_SPECIALIST = 12;
    const TYPE_STUDENT = 13;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'phone',
        'password',
        'firstname',
        'lastname',
        'middlename',
        'phone_verified_at',
        'email',
        'avatar',
        'status',
        'about',
        'site',
        'contacts',
        'birth_date',
        'region_id',
        'district_id',
        'locality_id',
        'school_id',
        'class_year',
        'class_form',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'phone_verified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone_verified_at' => 'datetime',
    ];

    protected $appends = [
        'avatar_src',
        'last_seen',
    ];

    public function accessGroup()
	{
		return $this->belongsToMany(AccessGroup::class, 'access_user', 'user_id', 'group_id');
	}

    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'user_specializations');
    }

	public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function job()
    {
        return $this->hasOne(UserJob::class);
    }

	public function ratedQuestions()
    {
        return $this->hasMany(RateQuestion::class);
    }

    public function ratedAnswers()
    {
        return $this->hasMany(RateAnswer::class);
    }

    public function ratedComments()
    {
        return $this->hasMany(QARateComment::class);
    }

    public function ratedArticles()
    {
        return $this->hasMany(RateArticle::class);
    }

    public function ratedKBComments()
    {
        return $this->hasMany(KBRateComment::class);
    }

    public function ratedCourseComments()
    {
        return $this->hasMany(RateCourseComment::class);
    }

    public function ratedLessonComments()
    {
        return $this->hasMany(RateLessonComment::class);
    }

    public function favQuestions()
    {
        return $this->belongsToMany(Question::class, 'user_fav_questions');
    }

    public function favArticles()
    {
        return $this->belongsToMany(Article::class, 'user_fav_articles');
    }

    public function favQAThemes()
    {
        return $this->belongsToMany(QATheme::class, 'user_fav_themes');
    }

    public function favKBThemes()
    {
        return $this->belongsToMany(KBTheme::class, 'user_fav_kb_themes');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'user_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id', 'id');
    }

    public function qaComments()
    {
        return $this->hasMany(QAComment::class, 'user_id', 'id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'user_id', 'id');
    }

    public function kbComments()
    {
        return $this->hasMany(KBComment::class, 'user_id', 'id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'user_cl_courses');
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'user_cl_lessons');
    }

    public function ratedCourses()
    {
        return $this->hasMany(RateCourse::class);
    }

    public function ratedLessons()
    {
        return $this->hasMany(RateLesson::class);
    }

    //Appends
    public function getAvatarSrcAttribute()
    {
        $avatar = null;

        if (!is_null($this->avatar)) {
            $avatar = env('APP_URL') . config('filesystems.disks.user.url') . $this->avatar;
        }

        return $avatar;
    }

    public function getSpecializationsAttribute()
    {
        $authorizedId = Auth::guard('api')->id();
        $specializations = $this->specializations()->get();

        $specializations->map(function ($specialization) use ($authorizedId) {
            $specialization->setAttribute('approvers_count', $specialization->approvers()->where('user_id', $this->id)->count());
            $specialization->setAttribute('is_approved',
                !is_null($authorizedId)
                    ? $specialization->approvers()->where('approver_id', $authorizedId)->where('user_id', $this->id)->exists()
                    : null
            );

            return $specialization;
        });

        return $specializations;
    }

    public function getRegionAttribute()
    {
        return $this->region()->get();
    }

    public function getDistrictAttribute()
    {
        return $this->district()->get();
    }

    public function getLocalityAttribute()
    {
        return $this->locality()->get();
    }

    public function getSchoolAttribute()
    {
        return $this->school()->get();
    }

    public function getJobAttribute()
    {
        return $this->job()->first();
    }

    public function getAnswersCountAttribute()
    {
        return $this->answers()->count();
    }

    public function getCommentsCountAttribute()
    {
        return $this->qaComments()->count();
    }

    public function getLastSeenAttribute()
    {
        $redis = Redis::connection();

        return $redis->get('last_seen_' . $this->id);
    }

    public function getProgressInSystemAttribute()
    {
        $redis = Redis::connection();
        $progress = $redis->get('progress_' . $this->id) ?? "0";
        $dt1 = new \DateTime("@0");
        $dt2 = new \DateTime("@$progress");

        return $dt1->diff($dt2)->format('%y-%m-%d %h:%i:%s');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function achievements()
    {
        return $this->hasMany(UserAchievement::class, 'user_id', 'id');
    }

    public function getAchievementPointsAttribute()
    {
        $sum = 0;

        foreach ($this->achievements as $achievement) {
            $sum += $achievement->achievement->points;
        }

        return $sum;
    }

    public function getRegistrationDateAttribute()
    {
        return $this->created_at;
    }
}
