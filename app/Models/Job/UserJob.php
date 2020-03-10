<?php

namespace App\Models\Job;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="UserJob",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="user_id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 *  @SWG\Property(property="domain_id", type="integer"),
 *  @SWG\Property(property="position", type="string"),
 *  @SWG\Property(property="start_date", type="string"),
 *  @SWG\Property(property="link", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class UserJob extends Model
{
    protected $table = 'user_jobs';

    protected $fillable = [
        'user_id',
        'name',
        'domain_id',
        'position',
        'start_date',
        'link',
    ];

    protected $appends = [
        'job_domain',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id', 'id');
    }

    public function getJobDomainAttribute()
    {
        return $this->domain()->first();
    }
}
