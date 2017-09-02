<?php

namespace App\Http\Controllers;

use App\Follower;
use App\Quote;
use App\UserFeed;
use Illuminate\Http\Request;

class ScheduledJobsController extends Controller
{
    public function feedQuotes(Request $request)
    {
        $quotes = Quote::where('active', 1)
            ->where('is_feeded', 0)
            ->get();
        foreach ($quotes as $quote) {
            $followers = Follower::where('user_id', $quote->user_id)->get();

            foreach ($followers as $follower) {
                $userFeed = new UserFeed();
                $userFeed->user_id = $follower->follower_id;
                $userFeed->quote_id = $quote->id;
                $userFeed->quote_user_id = $quote->user_id;
                $userFeed->save();
            }

            $quote->is_feeded = 1;
            $quote->save();
        }
    }
}
