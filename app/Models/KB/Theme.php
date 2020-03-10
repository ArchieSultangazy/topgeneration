<?php

namespace App\Models\KB;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="KBTheme",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Theme extends Model
{
    use SoftDeletes;

    protected $table = 'kb_themes';

    protected $fillable = [
        'name',
    ];

    protected $appends = [
        'user_favorite',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'kb_theme_articles');
    }

    public function favUsers()
    {
        return $this->belongsToMany(User::class, 'user_fav_kb_themes');
    }

    public function getUserFavoriteAttribute()
    {
        $favorite = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $favorite = $user->favKBThemes->contains($this->id);
        }

        return $favorite;
    }
}
