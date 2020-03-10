<?php

namespace App\Models\CL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *  definition="CLTheme",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class Theme extends Model
{
    use SoftDeletes;

    protected $table = 'cl_themes';

    protected $fillable = [
        'locale',
        'name',
    ];
}
