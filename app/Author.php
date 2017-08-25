<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = "users";

    protected $fillable = ['firebase_id', 'name', 'email','firebase_profile_image'];

    /**
     * Get the quotes of the author.
     */
    public function quotes()
    {
        return $this->hasMany('App\Quote');
    }

}
