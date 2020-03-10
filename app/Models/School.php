<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="School",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="region_id", type="integer"),
 *  @SWG\Property(property="locality_id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 * )
 */
class School extends Model
{
    protected $table = 'schools';

    protected $fillable = [
        'region_id',
        'locality_id',
        'name',
    ];

    public $timestamps = false;
}
