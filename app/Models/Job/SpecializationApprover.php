<?php

namespace App\Models\Job;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SpecializationApprover extends Model
{
    protected $fillable = [
        'user_id',
        'specialization_id',
        'approver_id',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }
}
