<?php

namespace App\Models\QA;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(
 *  definition="Theme",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Theme extends Model
{
    use SoftDeletes;

    protected $table = 'qa_themes';

    protected $fillable = [
        'name',
    ];

    protected $appends = [
        'user_favorite',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'qa_theme_questions');
    }

    public function favUsers()
    {
        return $this->belongsToMany(User::class, 'user_fav_themes');
    }

    public function getUserFavoriteAttribute()
    {
        $favorite = null;
        $user = Auth::guard('api')->user();

        if (!is_null($user)) {
            $favorite = $user->favQAThemes->contains($this->id);
        }

        return $favorite;
    }
}
