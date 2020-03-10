<?php

namespace App\Models\QA\Rating;

use Illuminate\Database\Eloquent\Model;

class RateQuestion extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'value',
    ];

    public $timestamps = false;
}
