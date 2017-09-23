<?php


namespace App\Http\Controllers;

use App\ApiResponse;
use App\Author;
use App\CanvasTheme;
use App\Category;
use App\Comment;
use App\CommentReport;
use App\Country;
use App\Follower;
use App\Language;
use App\Quote;
use App\QuoteCategory;
use App\QuoteLike;
use App\QuoteReport;
use App\ReportReason;
use App\UserFeed;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class ApiController extends Controller
{

    /**
     * ApiController constructor.
     */
    public function __construct()
    {
    }

    public function getLanguages(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $languages = Language::where('active', 1)
                ->orderBy('name', 'asc')
                ->get();

            foreach ($languages as $language) {
                $languageObject = app()->make('stdClass');
                $languageObject->languageId = (string)$language->id;
                $languageObject->languageName = $language->name;
                $languageObject->languageIsoCode = $language->iso_639_1;
                $response[] = $languageObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getReportReasons(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $reportReasons = ReportReason::where('active', 1)
                ->get();

            foreach ($reportReasons as $reportReason) {
                $reportReasonObject = app()->make('stdClass');
                $reportReasonObject->id = (string)$reportReason->id;
                $reportReasonObject->title = $reportReason->name;
                $response[] = $reportReasonObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getCountries(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $countries = Country::where('active', 1)
                ->orderBy('name', 'asc')
                ->get();

            foreach ($countries as $country) {
                $countryObject = app()->make('stdClass');
                $countryObject->countryId = (string)$country->id;
                $countryObject->countryName = $country->name;
                $countryObject->isoCode2 = $country->iso_code_2;
                $countryObject->isoCode3 = $country->iso_code_3;
                $response[] = $countryObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getCategories(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $categories = Category::where('active', 1)
                ->orderBy('name', 'asc')
                ->get();

            foreach ($categories as $category) {
                $categoryObject = app()->make('stdClass');
                $categoryObject->id = (string)$category->id;
                $categoryObject->name = $category->name;
                $response[] = $categoryObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getCanvasThemes(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $canvasThemes = CanvasTheme::where('active', 1)
                ->paginate(10);

            foreach ($canvasThemes as $canvasTheme) {
                $canvasThemesObject = app()->make('stdClass');
                $canvasThemesObject->id = (string)$canvasTheme->id;
                $canvasThemesObject->imageUrl = $canvasTheme->image;
                $canvasThemesObject->textColor = $canvasTheme->text_color;
                $canvasThemesObject->textFontFamily = $canvasTheme->text_font_family;
                $canvasThemesObject->textLocationX = $canvasTheme->text_location_x;
                $canvasThemesObject->textLocationY = $canvasTheme->text_location_y;
                $canvasThemesObject->textSize = $canvasTheme->text_size;
                $canvasThemesObject->textStyle = $canvasTheme->text_style;
                $response[] = $canvasThemesObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getAuthors(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $filterObject = json_decode($request->get("authorFilters"));
            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users

            $authorID = $filterObject->authorID;
            $filterType = $filterObject->filterType;//follower or following
            $page = $filterObject->page;// current page

            if ($filterType == "follower") {
                $followers = Follower::where('user_id', $authorID)
                    ->paginate(10, ['*'], 'page', $page);

                foreach ($followers as $user) {

                    $follower = Author::where('id', $user->follower_id)
                        ->where('active', 1)
                        ->select('id', 'name', 'profile_image')
                        ->first();

                    if ($follower != null) {
                        $authorObject = app()->make('stdClass');

                        $authorObject->id = (string)$follower->id;
                        $authorObject->name = $follower->name;
                        $authorObject->profileImage = $this->getUserProfileImageUrl($follower->id);

                        $isFollowing = Follower::where('user_id', $follower->id)
                            ->where('follower_id', $loggedAuthorID)
                            ->first();

                        if ($isFollowing != null) {
                            $authorObject->followingAuthor = true;
                        } else {
                            $authorObject->followingAuthor = false;
                        }
                        $response[] = $authorObject;
                    }
                }


            } else if ($filterType == "following") {
                $following = Follower::where('follower_id', $authorID)
                    ->paginate(10, ['*'], 'page', $page);

                foreach ($following as $user) {

                    $following = Author::where('id', $user->user_id)
                        ->where('active', 1)
                        ->select('id', 'name', 'profile_image')
                        ->first();

                    if ($following != null) {
                        $authorObject = app()->make('stdClass');
                        $authorObject->id = (string)$following->id;
                        $authorObject->name = $following->name;
                        $authorObject->profileImage = $this->getUserProfileImageUrl($following->id);

                        if ($loggedAuthorID == $authorID) { // User is seeing whom he followings
                            $authorObject->followingAuthor = true;
                        } else { // User is seeing other user followers
                            $isFollowing = Follower::where('user_id', $following->id)
                                ->where('follower_id', $loggedAuthorID)
                                ->first();

                            if ($isFollowing != null) {
                                $authorObject->followingAuthor = true;
                            } else {
                                $authorObject->followingAuthor = false;
                            }
                        }
                        $response[] = $authorObject;
                    }
                }
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getUserProfileImageUrl($user_id)
    {
        $author = Author::where('id', $user_id)
            ->select('firebase_profile_image', 'profile_image')
            ->first();
        if ($author->profile_image == null && $author->firebase_profile_image == null) {
            return $this->getDefaultProfileImage();
        } else if ($author->profile_image == null) {
            return $author->firebase_profile_image;
        } else {
            return $this->getAuthorThumbnailUrl($author->profile_image, false, 200, 200);
        }
    }

    public function getDefaultProfileImage()
    {
        return asset(config('app.dir_image') . config('app.dir_users_image') . config('app.default_profile_image'));
    }

    public function getAuthorThumbnailUrl($file_name, $is_original = false, $width, $height)
    {
        $original_file_path = config('app.dir_image') . config('app.dir_users_image') . $file_name;

        if ($is_original) {
            return asset($original_file_path);
        }

        $info = pathinfo($file_name);

        $extension = $info['extension'];

        //getting the image dimensions
        list($width_orig, $height_orig) = getimagesize($original_file_path);
        // Find the original ratio
        $ratio_orig = $width_orig / $height_orig;

        if ($ratio_orig >= 1) {

            $new_width = $width;
            $new_height = (int)($new_width / $ratio_orig);
        } else {
            $new_width = $width;
            $new_height = (int)($new_width * $ratio_orig);
        }

        $thumbnail_file_name = utf8_substr($file_name, 0, utf8_strrpos($file_name, '.')) . "-" . $new_width . "x" . $new_height . '.' . $extension;

        $thumbnail_file_path = config('app.dir_image') . config('app.dir_thumbnails') . $thumbnail_file_name;

        if (!file_exists($thumbnail_file_path)) {
            Image::make($original_file_path)->resize($new_width, $new_height)->save($thumbnail_file_path);
        }
        return asset($thumbnail_file_path);
    }

    public function getQuotes(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $filterObject = json_decode($request->get("quoteFilters"));

            if (isset($filterObject->filterType) && $filterObject->filterType == "feed") {
                return $this->getUserFeed($request);
            }

            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users


            $sql = DB::table("quotes")
                ->leftJoin('quote_categories', 'quotes.id', '=', 'quote_categories.quote_id')
                ->leftJoin('languages', 'quotes.language_id', '=', 'languages.id')
                ->leftJoin('users', 'quotes.user_id', '=', 'users.id')
                ->select('quotes.*', 'users.name as user_name', 'users.profile_image as user_profile_image');

            $sql->where("quotes.active", 1);
            $sql->where("users.active", 1);

            if (isset($filterObject->searchKeyword)) {
                $sql->where(function ($query) use ($filterObject) {
                    $query->orWhere('tags', 'like', '%' . $filterObject->searchKeyword . '%')
                        ->orWhere('caption', 'like', '%' . $filterObject->searchKeyword . '%')
                        ->orWhere('content', 'like', '%' . $filterObject->searchKeyword . '%');
                });
            }

            if (isset($filterObject->authorID)) {
                $sql->where('user_id', $filterObject->authorID);
            }
            if (isset($filterObject->filterType)) {
                if ($filterObject->filterType == "latest") {
                    $sql->orderBy('quotes.created_at', 'desc');
                } elseif ($filterObject->filterType == "trending") {
                    $sql->where('quotes.created_at', '>=', Carbon::now()->subDays(2));
                    $sql->orderBy('total_likes', 'desc');
                } elseif ($filterObject->filterType == "popular") {
                    $sql->orderBy('total_views', 'desc');
                } else {
                    $sql->orderBy('quotes.created_at', 'desc');
                }
            }

            if (isset($filterObject->categories)) {
                $categoryIds = array();
                foreach ($filterObject->categories as $category) {
                    $categoryIds[] = $category->id;
                }

                if (count($categoryIds)) {
                    $sql->whereIn('quote_categories.category_id', $categoryIds);
                }
            }
            if (isset($filterObject->languages)) {
                $languageIds = array();
                foreach ($filterObject->languages as $language) {
                    $languageIds[] = $language->languageId;
                }

                if (count($languageIds)) {
                    $sql->whereIn('languages.id', $languageIds);
                }
            }
            if (isset($filterObject->page)) {
                $sql->paginate(10, ['*'], 'page', $filterObject->page);
            }

            $quotes = $sql->distinct()->get();

            foreach ($quotes as $quote) {

                $quoteObject = app()->make('stdClass');

                $quoteObject->id = (string)$quote->id;
                /*$quoteObject->totalLikes = (string)$quote->total_likes;
                $quoteObject->totalComments = (string)$quote->total_comments;
                $quoteObject->totalViews = (string)$quote->total_views;*/

                $quoteObject->totalLikes = (string)QuoteLike::where('quote_id', $quote->id)->count();
                $quoteObject->totalComments = (string)Comment::where('quote_id', $quote->id)->where('active', 1)->count();
                $quoteObject->totalViews = (string)$quote->total_views;

                $isLiked = QuoteLike::where('quote_id', $quote->id)
                    ->where('user_id', $loggedAuthorID)
                    ->first();

                if ($isLiked) {
                    $quoteObject->likeQuote = true;
                } else {
                    $quoteObject->likeQuote = false;
                }

                $quoteObject->isCopyrighted = $quote->is_copyright ? true : false;
                $quoteObject->source = $quote->source;
                $quoteObject->imageUrl = $this->getQuoteThumbnailUrl($quote->image, false, 500, 500);
                $quoteObject->caption = base64_decode($quote->caption_encoded);
                $quoteObject->dateAdded = date('d-M-y h:i A', strtotime($quote->created_at));
                $quoteObject->tags = explode(',', $quote->tags);


                $quoteObject->author = app()->make('stdClass');
                $quoteObject->author->id = (string)$quote->user_id;
                $quoteObject->author->name = $quote->user_name;

                $isFollowing = Follower::where('user_id', $quote->user_id)
                    ->where('follower_id', $loggedAuthorID)
                    ->first();

                if ($isFollowing) {
                    $quoteObject->author->followingAuthor = true;
                } else {
                    $quoteObject->author->followingAuthor = false;
                }
                $quoteObject->author->profileImage = $this->getUserProfileImageUrl($quote->user_id);

                $response[] = $quoteObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getUserFeed(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $filterObject = json_decode($request->get("quoteFilters"));

            $loggedAuthorID = $request->get("loggedAuthorId");

            $sql = DB::table("user_feed")
                ->leftJoin('quotes', 'user_feed.quote_id', '=', 'quotes.id')
                ->leftJoin('users', 'user_feed.quote_user_id', '=', 'users.id')
                ->select('quotes.*', 'users.name as user_name', 'users.profile_image as user_profile_image');

            $sql->where("user_feed.user_id", $loggedAuthorID);
            $sql->where("quotes.active", 1);
            $sql->where("users.active", 1);
            $sql->orderBy('user_feed.quote_id', 'desc');

            $sql->paginate(10, ['*'], 'page', $filterObject->page);

            $quotes = $sql->get();

            foreach ($quotes as $quote) {

                $quoteObject = app()->make('stdClass');

                $quoteObject->id = (string)$quote->id;
                /*$quoteObject->totalLikes = (string)$quote->total_likes;
                $quoteObject->totalComments = (string)$quote->total_comments;*/
                $quoteObject->totalLikes = (string)QuoteLike::where('quote_id', $quote->id)->count();;
                $quoteObject->totalComments = (string)Comment::where('quote_id', $quote->id)->where('active', 1)->count();;
                $quoteObject->totalViews = (string)$quote->total_views;

                $isLiked = QuoteLike::where('quote_id', $quote->id)
                    ->where('user_id', $loggedAuthorID)
                    ->first();

                if ($isLiked) {
                    $quoteObject->likeQuote = true;
                } else {
                    $quoteObject->likeQuote = false;
                }

                $quoteObject->isCopyrighted = $quote->is_copyright ? true : false;
                $quoteObject->source = $quote->source;
                $quoteObject->imageUrl = $this->getQuoteThumbnailUrl($quote->image, false, 500, 500);
                $quoteObject->caption = base64_decode($quote->caption_encoded);
                $quoteObject->dateAdded = date('d-M-y h:i A', strtotime($quote->created_at));
                $quoteObject->tags = explode(',', $quote->tags);


                $quoteObject->author = app()->make('stdClass');
                $quoteObject->author->id = (string)$quote->user_id;
                $quoteObject->author->name = $quote->user_name;

                $isFollowing = Follower::where('user_id', $quote->user_id)
                    ->where('follower_id', $loggedAuthorID)
                    ->first();

                if ($isFollowing) {
                    $quoteObject->author->followingAuthor = true;
                } else {
                    $quoteObject->author->followingAuthor = false;
                }
                $quoteObject->author->profileImage = $this->getUserProfileImageUrl($quote->user_id);

                $response[] = $quoteObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getQuoteThumbnailUrl($file_name, $is_original, $width, $height)
    {

        $original_file_path = config('app.dir_image') . config('app.dir_quotes_image') . $file_name;

        if ($is_original) {
            return asset($original_file_path);
        }

        $info = pathinfo($file_name);

        $extension = $info['extension'];

        $thumbnail_file_name = utf8_substr($file_name, 0, utf8_strrpos($file_name, '.')) . "-" . $width . "x" . $height . '.' . $extension;

        $thumbnail_file_path = config('app.dir_image') . config('app.dir_thumbnails') . $thumbnail_file_name;

        if (!file_exists($thumbnail_file_path)) {
            Image::make($original_file_path)->resize($width, $height)->save($thumbnail_file_path);
        }
        return asset($thumbnail_file_path);
    }

    public function getQuote(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users
            $quoteId = $request->get("quoteId");

            $quote = Quote::where('id', $quoteId)
                ->where('active', 1)
                ->first();

            if (!$quote) {
                throw new \Exception("Quote not found");
            }

            $response->id = (string)$quote->id;
            /*$response->totalLikes = (string)$quote->total_likes;
            $response->totalComments = (string)$quote->total_comments;*/

            $response->totalLikes = (string)QuoteLike::where('quote_id', $quote->id)->count();;
            $response->totalComments = (string)Comment::where('quote_id', $quote->id)->where('active', 1)->count();;

            $response->totalViews = (string)$quote->total_views;

            $isLiked = QuoteLike::where('quote_id', $quoteId)
                ->where('user_id', $loggedAuthorID)
                ->first();

            if ($isLiked) {
                $response->likeQuote = true;
            } else {
                $response->likeQuote = false;
            }

            $response->isCopyrighted = $quote->is_copyright ? true : false;
            $response->source = $quote->source;
            $response->imageUrl = $this->getQuoteThumbnailUrl($quote->image, false, 1000, 1000);
            $response->caption = base64_decode($quote->caption_encoded);
            $response->dateAdded = $quote->created_at->format('d-M-y h:i A');
            $response->tags = trim($quote->tags) != "" ? explode(',', $quote->tags) : array();

            $author = Author::where('id', $quote->user_id)
                ->where('active', 1)
                ->select('id', 'name', 'profile_image')
                ->first();

            if ($author == null) {
                throw new \Exception("Author not found");
            }
            $response->author = app()->make('stdClass');
            $response->author->id = (string)$author->id;
            $response->author->name = $author->name;

            $isFollowing = Follower::where('user_id', $author->id)
                ->where('follower_id', $loggedAuthorID)
                ->first();

            if ($isFollowing) {
                $response->author->followingAuthor = true;
            } else {
                $response->author->followingAuthor = false;
            }
            $response->author->profileImage = $this->getUserProfileImageUrl($author->id);

            $language = Language::where('active', 1)
                ->where('id', $quote->language_id)
                ->first();

            $languageObject = app()->make('stdClass');

            $languageObject->languageId = (string)$language->id;
            $languageObject->languageName = $language->name;

            $response->language = $languageObject;

            $categories = DB::table("quote_categories")
                ->leftJoin('categories', 'quote_categories.category_id', '=', 'categories.id')
                ->select('categories.*')
                ->where("quote_categories.quote_id", $quote->id)
                ->where("categories.active", 1)
                ->get();

            foreach ($categories as $category) {
                $categoryObject = app()->make('stdClass');
                $categoryObject->id = (string)$category->id;
                $categoryObject->name = $category->name;
                $response->categories[] = $categoryObject;
            }

            $apiResponse->setResponse($response);

            //increment view

            Quote::where('id', $quote->id)
                ->update(['total_views' => $quote->total_views + 1]);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getComments(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $filterObject = json_decode($request->get("commentFilters"));

            $sql = DB::table("comments")
                ->leftJoin('users', 'comments.user_id', '=', 'users.id')
                ->select('comments.*', 'users.name as user_name', 'users.profile_image as user_profile_image');

            $sql->where("users.active", 1);

            if (isset($filterObject->quoteID)) {
                $sql->where('comments.quote_id', $filterObject->quoteID);
            }

            if (isset($filterObject->page)) {
                $sql->paginate(10, ['*'], 'page', $filterObject->page);
            }

            $comments = $sql->get();

            foreach ($comments as $comment) {

                $commentObject = app()->make('stdClass');

                $commentObject->id = (string)$comment->id;
                $commentObject->comment = base64_decode($comment->comment);
                $commentObject->dateAdded = date('d-M-y h:i A', strtotime($comment->created_at));

                $authorObject = app()->make('stdClass');

                $authorObject->id = (string)$comment->user_id;
                $authorObject->name = $comment->user_name;
                $authorObject->profileImage = $this->getUserProfileImageUrl($comment->user_id);

                $commentObject->author = $authorObject;

                $response[] = $commentObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function reportQuote(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $loggedAuthorID = $request->get("loggedAuthorId");
            $quoteID = $request->get("quoteId");
            $reportReasonID = $request->get("reportId");

            QuoteReport::firstOrCreate(['quote_id' => $quoteID, 'user_id' => $loggedAuthorID], ['report_reason_id' => $reportReasonID]);

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function likeQuote(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $loggedAuthorID = $request->get("loggedAuthorId");
            $quoteID = $request->get("quoteId");

            $likeExist = QuoteLike::where('quote_id', $quoteID)
                ->where('user_id', $loggedAuthorID)
                ->first();
            if ($likeExist == null) {
                $quoteLike = new QuoteLike;
                $quoteLike->quote_id = $quoteID;
                $quoteLike->user_id = $loggedAuthorID;
                $quoteLike->save();
            } else {
                $likeExist->delete();
            }
            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function followAuthor(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $loggedAuthorID = $request->get("loggedAuthorId");
            $authorID = $request->get("authorId");

            $isFollower = Follower::where('user_id', $authorID)
                ->where('follower_id', $loggedAuthorID)
                ->first();
            if ($isFollower == null) {
                $follower = new Follower();
                $follower->user_id = $authorID;
                $follower->follower_id = $loggedAuthorID;
                $follower->save();
                $this->saveFeed($follower->user_id, $follower->follower_id);
            } else {
                $this->deleteFeed($isFollower->user_id, $isFollower->follower_id);
                $isFollower->delete();
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function saveFeed($feeder_user_id, $target_user_id)
    {
        $quotes = Quote::where('user_id', $feeder_user_id)
            ->where('active', 1)
            ->where('is_feeded', 1)
            ->where('quotes.created_at', '>=', Carbon::now()->subMonths(2))
            ->select('id', 'user_id')
            ->get();

        foreach ($quotes as $quote) {

            $userFeed = new UserFeed();

            $userFeed->user_id = $target_user_id;
            $userFeed->quote_id = $quote->id;
            $userFeed->quote_user_id = $quote->user_id;

            $userFeed->save();
        }
    }

    public function deleteFeed($feeder_user_id, $target_user_id)
    {
        $deletedRows = UserFeed::where('quote_user_id', $feeder_user_id)
            ->where('user_id', $target_user_id)
            ->delete();
    }

    public function saveAuthor(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $author_data = json_decode($request->get("author"));

            $authorArray = array();

            if (isset($author_data->firebaseId)) {
                $authorArray['firebase_id'] = $author_data->firebaseId;
            }

            if (isset($author_data->name)) {
                $authorArray['name'] = $author_data->name;
            }

            if (isset($author_data->email)) {
                $authorArray['email'] = $author_data->email;
            }

            if (isset($author_data->profileImage)) {
                $authorArray['firebase_profile_image'] = $author_data->profileImage;
            }

            $author = Author::updateOrCreate(
                ['firebase_id' => $author_data->firebaseId], $authorArray
            );

            $request->request->add(['authorId' => $author->id]);
            $request->request->add(['loggedAuthorId' => $author->id]);

            return $this->getAuthor($request);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getAuthor(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users
            $authorId = $request->get("authorId");

            $author = Author::where('id', $authorId)
                ->where('active', 1)
                ->first();

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $response->id = (string)$author->id;
            $response->firebaseId = $author->firebase_id;
            $response->name = $author->name;
            $response->gender = $author->gender;
            $response->dob = $author->dob;
            $response->mobile = $author->mobile;
            $response->email = $author->email;
            $response->profileImage = $this->getUserProfileImageUrl($author->id);
            $response->coverImage = $this->getUserCoverImageUrl($author->id);

            $response->status = base64_decode($author->status);
            $response->totalQuotes = Quote::where('user_id', $authorId)
                ->count();
            $response->totalLikes = 0;
            $response->totalFollowers = DB::table("followers")
                ->leftJoin('users', 'users.id', '=', 'followers.follower_id')
                ->where('followers.user_id', $authorId)
                ->where('users.active', 1)
                ->count();
            $response->totalFollowing = DB::table("followers")
                ->leftJoin('users', 'users.id', '=', 'followers.user_id')
                ->where('followers.follower_id', $authorId)
                ->where('users.active', 1)
                ->count();

            $isFollowing = Follower::where('user_id', $authorId)
                ->where('follower_id', $loggedAuthorID)
                ->first();

            if ($isFollowing != null) {
                $response->followingAuthor = true;
            } else {
                $response->followingAuthor = false;
            }

            $response->dateCreated = $author->created_at->format('d-M-y h:i A');

            $country = Country::where('id', $author->country_id)
                ->where('active', 1)
                ->first();

            if ($country != null) {

                $countryObject = app()->make('stdClass');
                $countryObject->countryId = (string)$country->id;
                $countryObject->countryName = $country->name;

                $response->country = $countryObject;
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getUserCoverImageUrl($user_id)
    {
        $author = Author::where('id', $user_id)
            ->select('cover_image')
            ->first();

        if ($author->cover_image == null) {
            return $this->getDefaultCoverImage();
        } else {
            return $this->getAuthorThumbnailUrl($author->cover_image, false, 1000, 800);
        }
    }

    public function getDefaultCoverImage()
    {
        return asset(config('app.dir_image') . config('app.dir_users_image') . config('app.default_cover_image'));
    }

    public function updateAuthor(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $author_data = json_decode($request->get("author"));

            $author = Author::find($author_data->id);

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $author->name = $author_data->name;
            $author->email = $author_data->email;
            $author->mobile = $author_data->mobile;
            $author->dob = date('Y-m-d', strtotime($author_data->dob));
            $author->gender = $author_data->gender;
            $author->status = base64_encode($author_data->status);

            $author->save();

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);


        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function updateProfileImage(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $authorId = $request->get("authorId");

            $author = Author::find($authorId);

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $profile_image = base64_decode($request->get("profileImage"));

            $file_name = $author->id . "-" . time() . ".JPG";

            $result = file_put_contents(config('app.dir_image') . config('app.dir_users_image') . $file_name, $profile_image);

            $this->generateAuthorThumbnails($file_name, "profile");

            if ($result) {
                $author->profile_image = $file_name;
                $author->save();
            } else {
                throw new \Exception("Oops! something went wrong. Please try again");
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function generateAuthorThumbnails($file_name, $type)
    {
        if ($type = "profile") {
            $thumbnail_sizes = array(
                array(
                    "width" => 200,
                    "height" => 200
                ),
                array(
                    "width" => 500,
                    "height" => 500
                )
            );
        } else if ($type == "cover") {
            $thumbnail_sizes = array(
                array(
                    "width" => 1000,
                    "height" => 800
                ),
                array(
                    "width" => 700,
                    "height" => 550
                )
            );
        }

        $original_file_path = config('app.dir_image') . config('app.dir_users_image') . $file_name;

        $info = pathinfo($file_name);

        $extension = $info['extension'];

        //getting the image dimensions
        list($width_orig, $height_orig) = getimagesize($original_file_path);
        // Find the original ratio
        $ratio_orig = $width_orig / $height_orig;


        foreach ($thumbnail_sizes as $thumbnail_size) {

            // Check whether to scale initially by height or by width
            if ($thumbnail_size['width'] / $thumbnail_size['height'] > $ratio_orig) {
                $new_height = $thumbnail_size['width'] / $ratio_orig;
                $new_width = $thumbnail_size['width'];
            } else {
                $new_width = $thumbnail_size['height'] * $ratio_orig;
                $new_height = $thumbnail_size['height'];
            }

            $thumbnail_file_name = utf8_substr($file_name, 0, utf8_strrpos($file_name, '.')) . "-" . $new_width . "x" . $new_height . '.' . $extension;

            $thumbnail_file_path = config('app.dir_image') . config('app.dir_thumbnails') . $thumbnail_file_name;

            Image::make($original_file_path)->resize($new_width, $new_height)->save($thumbnail_file_path);
        }
    }

    public function updateCoverImage(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $authorId = $request->get("authorId");

            $author = Author::find($authorId);

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $cover_image = base64_decode($request->get("coverImage"));

            $file_name = $author->id . "-" . time() . ".JPG";

            $result = file_put_contents(config('app.dir_image') . config('app.dir_users_image') . $file_name, $cover_image);

            $this->generateAuthorThumbnails($file_name, "cover");

            if ($result) {
                $author->cover_image = $file_name;
                $author->save();
            } else {
                throw new \Exception("Oops! something went wrong. Please try again");
            }

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function updateUserCountry(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $authorId = $request->get("authorId");
            $countryId = $request->get("countryId");

            $author = Author::find($authorId);

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $author->country_id = $countryId;
            $author->save();

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function saveComment(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $comment = new Comment();

            $comment->quote_id = $request->get("quoteId");
            $comment->user_id = $request->get("authorId");
            $comment->comment = base64_encode($request->get("comment"));
            $comment->save();

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function saveQuote(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $quote_data = json_decode($request->get('quote'));

            $quote_image = base64_decode($request->get('quoteImage'));

            $quote = new Quote();

            $quote->user_id = $quote_data->author->id;
            $quote->content = implode(',', $quote_data->content);
            $quote->language_id = $quote_data->language->languageId;
            $quote->caption = $quote_data->caption;
            $quote->caption_encoded = base64_encode($quote_data->caption);
            $quote->source = $quote_data->source;
            $quote->tags = implode(',', $quote_data->tags);
            $quote->is_copyright = $quote_data->isCopyrighted ? 1 : 0;

            $file_name = $quote_data->author->id . "-" . time() . ".JPG";
            $file_path = config('app.dir_image') . config('app.dir_quotes_image') . $file_name;

            $result = file_put_contents($file_path, $quote_image);
            $this->generateQuoteThumbnails($file_name);

            if ($result) {
                $quote->image = $file_name;
                $quote->save();

                foreach ($quote_data->categories as $category) {
                    $quoteCategory = new QuoteCategory();
                    $quoteCategory->quote_id = $quote->id;
                    $quoteCategory->category_id = $category->id;
                    $quoteCategory->save();
                }
            } else {
                throw new \Exception("Oops! something went wrong. Please try again");
            }
            $response->id = (string)$quote->id;
            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function generateQuoteThumbnails($file_name)
    {

        $thumbnail_sizes = array(
            array(
                "width" => 300,
                "height" => 300
            ),
            array(
                "width" => 500,
                "height" => 500
            ),
            array(
                "width" => 700,
                "height" => 700
            ),
            array(
                "width" => 1000,
                "height" => 1000
            ),
        );

        $original_file_path = config('app.dir_image') . config('app.dir_quotes_image') . $file_name;

        $info = pathinfo($file_name);

        $extension = $info['extension'];

        foreach ($thumbnail_sizes as $thumbnail_size) {

            $thumbnail_file_name = utf8_substr($file_name, 0, utf8_strrpos($file_name, '.')) . "-" . $thumbnail_size['width'] . "x" . $thumbnail_size['height'] . '.' . $extension;

            $thumbnail_file_path = config('app.dir_image') . config('app.dir_thumbnails') . $thumbnail_file_name;

            Image::make($original_file_path)->resize($thumbnail_size['width'], $thumbnail_size['height'])->save($thumbnail_file_path);
        }
    }

    public function reportComment(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $loggedAuthorID = $request->get("loggedAuthorId");
            $commentID = $request->get("commentId");
            $reportReasonID = $request->get("reportId");

            CommentReport::firstOrCreate(['comment_id' => $commentID, 'user_id' => $loggedAuthorID], ['report_reason_id' => $reportReasonID]);

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getQuotesImageUrl($imagePath)
    {
        return asset(config('app.dir_image') . config('app.dir_quotes_image') . $imagePath);
    }

}
