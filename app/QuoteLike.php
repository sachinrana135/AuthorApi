<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteLike extends Model
{
    protected $table = "quote_likes";

    protected $fillable = ['quote_id','user_id'];
}
