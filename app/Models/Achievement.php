<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *  definition="Achievement",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="ru_name", type="string"),
 *  @SWG\Property(property="kk_name", type="string"),
 *  @SWG\Property(property="en_name", type="string"),
 *  @SWG\Property(property="key", type="string"),
 *  @SWG\Property(property="points", type="integer"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 *  @SWG\Property(property="deleted_at", type="string"),
 * )
 */

/**
 * Class Achievement
 * @package App\Models
 *
 * @property integer $id
 * @property string $ru_name
 * @property string $kk_name
 * @property string $en_name
 * @property string $key
 * @property integer $points
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 */
class Achievement extends Model
{
    use SoftDeletes;

    const PROFILE_PHOTO_ACHIEVEMENT = 'profile_photo',
          PROFILE_REGION = 'profile_region',
          PROFILE_BRIEF = 'profile_brief',
          PROFILE_FILLED_ALL = 'profile_info';


    protected $table = 'achievements';

    public static $userProfileAchievements = [
        self::PROFILE_PHOTO_ACHIEVEMENT,
        self::PROFILE_REGION,
        self::PROFILE_BRIEF,
        self::PROFILE_FILLED_ALL,
    ];
}
