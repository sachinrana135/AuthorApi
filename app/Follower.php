<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $table = "followers";

    protected $fillable = ['user_id','follower_id'];

    /**
     * Get the phone record associated with the user.
     */
    public function Follower()
    {
        return $this->hasOne('App\Author', 'id',"follower_id")->where('active',"=",1);
    }

    /**
     * Get the phone record associated with the user.
     */
    public function Following()
    {
        return $this->hasOne('App\Author', 'id','user_id')->where('active','=',1);
    }
}
