<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $table = "quotes";

    /**
     * Get the author of quote.
     */
    public function Author()
    {
        return $this->belongsTo('App\Author','user_id',"id")->where('active',"=",1);
    }

    /**
     * Get the language of quote.
     */
    public function Language()
    {
        return $this->belongsTo('App\Language','language_id',"id")->where('active',"=",1);
    }

    /**
     * Get the categories of quote.
     */
    public function Categories()
    {
        return $this->belongsToMany('App\Category','quote_categories',"quote_id",'category_id')->where('active',"=",1);
    }

}
