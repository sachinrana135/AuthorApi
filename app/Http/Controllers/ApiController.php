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

            $languages = Language::where('active', 1)
                ->orderBy('name', 'asc')
                ->get();

            $response = array();

            foreach ($languages as $language) {
                $languageObject = app()->make('stdClass');
                $languageObject->languageId = (string)$language->id;
                $languageObject->languageName = $language->name;
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

            $reportReasons = ReportReason::where('active', 1)
                ->get();

            $response = array();

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

            $countries = Country::where('active', 1)
                ->orderBy('name', 'asc')
                ->get();

            $response = array();

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

            $categories = Category::where('active', 1)
                ->orderBy('name', 'asc')
                ->get();

            $response = array();

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

            $canvasThemes = CanvasTheme::where('active', 1)
                ->paginate(10);

            $response = array();

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

            $filterObject = json_decode($request->get("authorFilters"));
            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users

            $authorID = $filterObject->authorID;
            $filterType = $filterObject->filterType;//follower or following
            $page = $filterObject->page;// current page

            $response = array();

            if ($filterType == "follower") {
                $followers = Follower::where('user_id', $authorID)
                    ->paginate(10, ['*'], 'page', $page);

                foreach ($followers as $follower) {

                    if ($follower->Follower != null) {
                        $authorObject = app()->make('stdClass');

                        $authorObject->id = (string)$follower->Follower->id;
                        $authorObject->name = $follower->Follower->name;
                        $authorObject->profileImage = $follower->Follower->profile_image;

                        $isFollowing = Follower::where('user_id', $follower->Follower->id)
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

                foreach ($following as $author) {
                    if ($author->Following != null) {
                        $authorObject = app()->make('stdClass');
                        $authorObject->id = (string)$author->Following->id;;
                        $authorObject->name = $author->Following->name;
                        $authorObject->profileImage = $author->Following->profile_image;

                        if ($loggedAuthorID == $authorID) { // User is seeing whom he followings
                            $authorObject->followingAuthor = true;
                        } else { // User is seeing other user followers
                            $isFollowing = Follower::where('user_id', $author->Following->id)
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

    public function getAuthor(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users
            $authorId = $request->get("authorId");

            $author = Author::where('id', $authorId)
                ->where('active', 1)
                ->first();

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $authorObject = app()->make('stdClass');
            $authorObject->id = (string)$author->id;
            $authorObject->firebaseId = $author->firebase_id;
            $authorObject->name = $author->name;
            $authorObject->gender = $author->gender;
            $authorObject->dob = $author->dob;
            $authorObject->mobile = $author->mobile;
            $authorObject->email = $author->email;
            if ($author->profile_image == null && $author->firebase_profile_image == null) {
                $authorObject->profileImage = $this->getUsersImageUrl(config('app.default_profile_image'));
            } else if ($author->profile_image == null) {
                $authorObject->profileImage = $author->firebase_profile_image;
            } else {
                $authorObject->profileImage = $this->getUsersImageUrl($author->profile_image);
            }

            if ($author->cover_image == null) {
                $authorObject->coverImage = $this->getUsersImageUrl(config('app.default_cover_image'));
            } else {
                $authorObject->coverImage = $this->getUsersImageUrl($author->cover_image);
            }
            $authorObject->status = $author->status;
            $authorObject->totalQuotes = Quote::where('user_id', $authorId)
                ->count();
            $authorObject->totalLikes = 0;
            $authorObject->totalFollowers = DB::table("followers")
                ->leftJoin('users', 'users.id', '=', 'followers.follower_id')
                ->where('followers.user_id', $authorId)
                ->where('users.active', 1)
                ->count();
            $authorObject->totalFollowing = DB::table("followers")
                ->leftJoin('users', 'users.id', '=', 'followers.user_id')
                ->where('followers.follower_id', $authorId)
                ->where('users.active', 1)
                ->count();

            $isFollowing = Follower::where('user_id', $authorId)
                ->where('follower_id', $loggedAuthorID)
                ->first();

            if ($isFollowing != null) {
                $authorObject->followingAuthor = true;
            } else {
                $authorObject->followingAuthor = false;
            }

            $authorObject->dateCreated = $author->created_at->format('d-M-y h:i A');

            $country = Country::where('id', $author->country_id)
                ->where('active', 1)
                ->first();

            if ($country != null) {

                $countryObject = app()->make('stdClass');
                $countryObject->countryId = (string)$country->id;
                $countryObject->countryName = $country->name;

                $authorObject->country = $countryObject;
            }

            $apiResponse->setResponse($authorObject);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function getQuotes(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $filterObject = json_decode($request->get("quoteFilters"));

            if(isset($filterObject->filterType) && $filterObject->filterType == "feed") {
                return $this->getUserFeed($request);
            }
            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users

            $sql = DB::table("quotes")
                ->leftJoin('quote_categories', 'quotes.id', '=', 'quote_categories.quote_id')
                ->leftJoin('categories', 'quote_categories.category_id', '=', 'categories.id')
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
                    $sql->whereIn('categories.id', $categoryIds);
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

            $quotes = $sql->get();
            $response = array();
            foreach ($quotes as $quote) {

                $quoteObject = app()->make('stdClass');

                $quoteObject->id = (string)$quote->id;
                $quoteObject->totalLikes = (string)$quote->total_likes;
                $quoteObject->totalComments = (string)$quote->total_comments;
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
                $quoteObject->imageUrl = $this->getQuotesImageUrl($quote->image);
                $quoteObject->caption = $quote->caption;
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
                $quoteObject->author->profileImage = $this->getUsersImageUrl($quote->user_profile_image);

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
            $response = array();
            foreach ($quotes as $quote) {

                $quoteObject = app()->make('stdClass');

                $quoteObject->id = (string)$quote->id;
                $quoteObject->totalLikes = (string)$quote->total_likes;
                $quoteObject->totalComments = (string)$quote->total_comments;
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
                $quoteObject->imageUrl = $this->getQuotesImageUrl($quote->image);
                $quoteObject->caption = $quote->caption;
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
                $quoteObject->author->profileImage = $this->getUsersImageUrl($quote->user_profile_image);

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

    public function getQuote(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $loggedAuthorID = $request->get("loggedAuthorId");// use of this variable is to determine whether current logged user following others users
            $quoteId = $request->get("quoteId");

            $quote = Quote::where('id', $quoteId)
                ->where('active', 1)
                ->first();

            if (!$quote) {
                throw new \Exception("Quote not found");
            }

            $quoteObject = app()->make('stdClass');

            $quoteObject->id = (string)$quote->id;
            $quoteObject->totalLikes = (string)$quote->total_likes;
            $quoteObject->totalComments = (string)$quote->total_comments;
            $quoteObject->totalViews = (string)$quote->total_views;

            $isLiked = QuoteLike::where('quote_id', $quoteId)
                ->where('user_id', $loggedAuthorID)
                ->first();

            if ($isLiked) {
                $quoteObject->likeQuote = true;
            } else {
                $quoteObject->likeQuote = false;
            }

            $quoteObject->isCopyrighted = $quote->is_copyright ? true : false;
            $quoteObject->source = $quote->source;
            $quoteObject->imageUrl = $this->getQuotesImageUrl($quote->image);
            $quoteObject->caption = $quote->caption;
            $quoteObject->dateAdded = $quote->created_at->format('d-M-y h:i A');
            $quoteObject->tags = explode(',', $quote->tags);

            if ($quote->Author == null) {
                throw new \Exception("Author not found");
            }
            $quoteObject->author = app()->make('stdClass');
            $quoteObject->author->id = (string)$quote->Author->id;
            $quoteObject->author->name = $quote->Author->name;

            $isFollowing = Follower::where('user_id', $quote->Author->id)
                ->where('follower_id', $loggedAuthorID)
                ->first();

            if ($isFollowing) {
                $quoteObject->author->followingAuthor = true;
            } else {
                $quoteObject->author->followingAuthor = false;
            }
            $quoteObject->author->profileImage = $this->getUsersImageUrl($quote->Author->profile_image);

            $languageObject = app()->make('stdClass');

            $languageObject->languageId = (string)$quote->Language->id;
            $languageObject->languageName = $quote->Language->name;

            $quoteObject->language = $languageObject;

            $categories = array();

            foreach ($quote->Categories as $category) {
                $categoryObject = app()->make('stdClass');
                $categoryObject->id = (string)$category->id;
                $categoryObject->name = $category->name;
                $categories[] = $categoryObject;
            }
            $quoteObject->categories = $categories;

            $apiResponse->setResponse($quoteObject);

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

            $filterObject = json_decode($request->get("commentFilters"));

            $sql = DB::table("comments")
                ->leftJoin('users', 'comments.user_id', '=', 'users.id')
                ->select('comments.*', 'users.name as user_name', 'users.profile_image as user_profile_image');

            $sql->where("users.active", 1);

            if (isset($filterObject->$filterObject->quoteID)) {
                $sql->where('comments.quote_id', $filterObject->quoteID);
            }

            if (isset($filterObject->page)) {
                $sql->paginate(10, ['*'], 'page', $filterObject->page);
            }

            $comments = $sql->get();
            foreach ($comments as $comment) {

                $commentObject = app()->make('stdClass');

                $commentObject->id = (string)$comment->id;
                $commentObject->comment = $comment->comment;
                $commentObject->dateAdded = date('d-M-y h:i A', strtotime($comment->created_at));

                $authorObject = app()->make('stdClass');

                $authorObject->id = (string)$comment->user_id;
                $authorObject->name = $comment->user_name;
                $authorObject->profileImage = $comment->user_profile_image;

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

            $response = "";

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

            $response = "";

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

            $response = "";

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
                $this->saveFeed($follower->user_id,$follower->follower_id);
            } else {
                $this->deleteFeed($isFollower->user_id,$isFollower->follower_id);
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

    public function saveAuthor(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = "";

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

    public function updateAuthor(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = "";

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
            $author->status = $author_data->status;

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

            $response = "";

            $authorId = $request->get("authorId");

            $author = Author::find($authorId);

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $profile_image = base64_decode($request->get("profileImage"));

            $file_name = $author->id . "-" . time() . ".JPG";

            $result = file_put_contents(config('app.dir_image') . config('app.dir_users_image') . $file_name, $profile_image);

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

    public function updateCoverImage(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = "";

            $authorId = $request->get("authorId");

            $author = Author::find($authorId);

            if ($author == null) {
                throw new \Exception("Author not found");
            }

            $cover_image = base64_decode($request->get("coverImage"));

            $file_name = $author->id . "-" . time() . ".JPG";

            $result = file_put_contents(config('app.dir_image') . config('app.dir_users_image') . $file_name, $cover_image);

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

            $response = "";

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

            $response = "";
            $comment = new Comment();

            $comment->quote_id = $request->get("quoteId");
            $comment->user_id = $request->get("authorId");
            $comment->comment = $request->get("comment");
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

            $response = "";

            $quote_data = json_decode($request->get('quote'));

            $quote_image = base64_decode($request->get('quoteImage'));

            $quote = new Quote();

            $quote->user_id = $quote_data->author->id;
            $quote->content = implode(',', $quote_data->content);
            $quote->language_id = $quote_data->language->languageId;
            $quote->caption = $quote_data->caption;
            $quote->source = $quote_data->source;
            $quote->tags = implode(',', $quote_data->tags);
            $quote->is_copyright = $quote_data->isCopyrighted ? 1 : 0;

            $file_name = $quote_data->author->id . "-" . time() . ".JPG";

            $result = file_put_contents(config('app.dir_image') . config('app.dir_quotes_image') . $file_name, $quote_image);

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

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);

        }
    }

    public function reportComment(Request $request)
    {
        $apiResponse = new ApiResponse();
        try {

            $response = "";

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

    public function getUsersImageUrl($imagePath)
    {
        return asset(config('app.dir_image') . config('app.dir_users_image') . $imagePath);
    }

    public function getQuotesImageUrl($imagePath)
    {
        return asset(config('app.dir_image') . config('app.dir_quotes_image') . $imagePath);
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

}
