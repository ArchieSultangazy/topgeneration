<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *  definition="UserAchievement",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="achievement_id", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 *  @SWG\Property(property="deleted_at", type="string"),
 * )
 */

/**
 * Class UserAchievement
 * @package App
 *
 * @property integer $user_id
 * @property integer $achievement_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 */
class UserAchievement extends Model
{
    use SoftDeletes;

    protected $table = 'user_achievements';

    public function achievement()
    {
        return $this->hasOne(Achievement::class, 'id', 'achievement_id');
    }
}
