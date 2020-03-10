<?php

namespace App\Models\KB\Rating;

use Illuminate\Database\Eloquent\Model;

class RateArticle extends Model
{
    protected $fillable = [
        'user_id',
        'article_id',
        'value',
    ];

    public $timestamps = false;
}
