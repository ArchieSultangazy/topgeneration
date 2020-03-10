<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="Locality",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 * )
 */
class Locality extends Model
{
    protected $table = 'location_localities';

    protected $fillable = [
        'region_id',
        'district_id',
        'name',
    ];

    public $timestamps = false;
}
