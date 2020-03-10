<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="Region",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 * )
 */
class Region extends Model
{
    protected $table = 'location_region';

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}
