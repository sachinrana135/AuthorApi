<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteCategory extends Model
{
    protected $table = "quote_categories";

    /**
     * Get the categories of quote.
     */
    public function Category()
    {
        return $this->belongsTo('App\Category','category_id',"id")->where('active',"=",1);
    }
}
