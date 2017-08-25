<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteReport extends Model
{
    protected $table = "quote_reports";

    protected $fillable = ['quote_id', 'report_reason_id', 'user_id'];
}
