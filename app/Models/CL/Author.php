<?php

namespace App\Models\CL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *  definition="Author",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="firstname", type="string"),
 *  @SWG\Property(property="lastname", type="string"),
 *  @SWG\Property(property="middlename", type="string"),
 *  @SWG\Property(property="about", type="string"),
 *  @SWG\Property(property="avatar", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Author extends Model
{
    use SoftDeletes;

    protected $table = 'cl_authors';

    protected $fillable = [
        'firstname',
        'lastname',
        'middlename',
        'about',
        'avatar',
    ];

    protected $appends = [
        'img_src',
    ];

    public function getImgSrcAttribute()
    {
        $img = null;

        if (!is_null($this->img_preview)) {
            $img = env('APP_URL') . config('filesystems.disks.cl_author.url') . $this->img_preview;
        }

        return $img;
    }
}
