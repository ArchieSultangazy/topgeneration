<?php

namespace App\Models\KB\Rating;

use Illuminate\Database\Eloquent\Model;

class RateComment extends Model
{
    protected $table = 'rate_kb_comments';

    protected $fillable = [
        'user_id',
        'comment_id',
        'value',
    ];

    public $timestamps = false;
}
