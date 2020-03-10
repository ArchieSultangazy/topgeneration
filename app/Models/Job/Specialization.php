<?php

namespace App\Models\Job;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="Specialization",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="locale", type="string"),
 *  @SWG\Property(property="name", type="string"),
 * )
 */
class Specialization extends Model
{
    protected $table = 'specializations';

    protected $fillable = [
        'locale',
        'name',
    ];

    public $timestamps = false;

    public function approvers()
    {
        return $this->hasMany(SpecializationApprover::class);
    }
}
