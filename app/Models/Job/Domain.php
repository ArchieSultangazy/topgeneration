<?php

namespace App\Models\Job;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="JobDomain",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="locale", type="string"),
 *  @SWG\Property(property="name", type="string"),
 * )
 */
class Domain extends Model
{
    protected $table = 'job_domains';

    protected $fillable = [
        'locale',
        'name',
    ];

    public $timestamps = false;
}
