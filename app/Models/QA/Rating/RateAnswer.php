<?php

namespace App\Models\QA\Rating;

use Illuminate\Database\Eloquent\Model;

class RateAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'answer_id',
        'value',
    ];

    public $timestamps = false;
}
