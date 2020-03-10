<?php

namespace App\Models\KB;

use App\Models\KB\Rating\RateArticle;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="Article",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="locale", type="string"),
 *  @SWG\Property(property="is_published", type="integer"),
 *  @SWG\Property(property="type", type="string"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="title", type="string"),
 *  @SWG\Property(property="body", type="string"),
 *  @SWG\Property(property="video", type="string"),
 *  @SWG\Property(property="rating", type="integer"),
 *  @SWG\Property(property="views", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Article extends Model
{
    use SoftDeletes;

    protected $table = 'kb_articles';

    protected $fillable = [
        'locale',
        'is_published',
        'type',
        'user_id',
        'title',
        'body',
        'img_preview',
        'video',
        'rating',
        'views',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'img_src',
        'video_src',
        'user_rate',
        'user_favorite',
        'comments_count',
    ];

    const TYPE_TEXT = 'text';
    const TYPE_VIDEO_IN = 'video_in';   //Videos which are stored in server
    const TYPE_VIDEO_OUT = 'video_out'; //Videos which are stored outside (like: youtube, rutube and etc.)

    //Relations
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function themes()
    {
        return $this->belongsToMany(Theme::class, 'kb_theme_articles');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'article_id', 'id');
    }

    public function ratedUsers()
    {
        return $this->hasMany(RateArticle::class);
    }

    public function favUsers()
    {
        return $this->belongsToMany(User::class, 'user_fav_articles');
    }

    //Attributes
    public function getAuthorAttribute()
    {
        return $this->author()->first();
    }

    public function getThemesAttribute()
    {
        return $this->themes()->get();
    }

    public function getCommentsAttribute()
    {
        return $this->comments()->whereNull('parent_id')->get();
    }

    public function getFavCountAttribute()
    {
        return $this->favUsers()->count();
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getUserRateAttribute()
    {
        $rate = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $ratedArticle = $user->ratedArticles()->where('article_id', $this->id)->first();
            $rate = !is_null($ratedArticle) ? $ratedArticle->value : null;
        }

        return $rate;
    }

    public function getUserFavoriteAttribute()
    {
        $favorite = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $favorite = $user->favArticles->contains($this->id);
        }

        return $favorite;
    }

    public static function getAvailableTypes()
    {
        return [
            self::TYPE_TEXT => 'Текстовая статья',
            self::TYPE_VIDEO_IN => 'Видео статья (Загрузка)',
            self::TYPE_VIDEO_OUT => 'Видео статья (Внешняя ссылка)',
        ];
    }

    public function getImgSrcAttribute()
    {
        $img = null;

        if (!is_null($this->img_preview)) {
            $img = env('APP_URL') . config('filesystems.disks.kb_article.url') . $this->img_preview;
        }

        return $img;
    }

    public function getVideoSrcAttribute()
    {
        $video = null;

        if ($this->type == Article::TYPE_VIDEO_IN) {
            $video = env('APP_URL') . config('filesystems.disks.kb_article.url') . $this->video;
        }

        return $video;
    }
}
