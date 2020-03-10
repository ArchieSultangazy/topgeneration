<?php

namespace App\Models\CL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *  definition="LessonFile",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="lesson_id", type="integer"),
 *  @SWG\Property(property="title", type="string"),
 *  @SWG\Property(property="body", type="string"),
 *  @SWG\Property(property="link", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class LessonFile extends Model
{
    use SoftDeletes;

    protected $table = 'cl_lesson_files';

    protected $fillable = [
        'lesson_id',
        'title',
        'body',
        'link',
    ];

    protected $appends = [
        'link_src',
    ];

    public function getLinkSrcAttribute()
    {
        $link = null;

        if (!is_null($this->link)) {
            $link = env('APP_URL') . config('filesystems.disks.cl_lesson.url') . $this->link;
        }

        return $link;
    }
}
