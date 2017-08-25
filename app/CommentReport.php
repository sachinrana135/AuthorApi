<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommentReport extends Model
{
    protected $table = "comment_reports";

    protected $fillable = ['comment_id', 'report_reason_id', 'user_id'];
}
