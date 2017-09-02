<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $table = "followers";

    protected $fillable = ['user_id','follower_id'];
}
