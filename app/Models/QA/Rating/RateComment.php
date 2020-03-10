<?php

namespace App\Models\QA\Rating;

use Illuminate\Database\Eloquent\Model;

class RateComment extends Model
{
    protected $fillable = [
        'user_id',
        'comment_id',
        'value',
    ];

    public $timestamps = false;
}
