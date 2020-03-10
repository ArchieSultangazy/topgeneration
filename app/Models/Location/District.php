<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="District",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 * )
 */
class District extends Model
{
    protected $table = 'location_districts';

    protected $fillable = [
        'region_id',
        'name',
    ];

    public $timestamps = false;
}
