<?php

namespace App\Http\Middleware;

use App\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Log;


class ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        //getting header parameters
        $apiToken = $request->header("apiToken");
        $appVersionName = $request->header("appVersionName");
        $appVersionCode = $request->header("appVersionCode");
        $apiCallSource = $request->header("callSource");
        $correlationId = $request->header("correlationId");
        $deviceId = $request->header("deviceId");


        $apiResponse = new ApiResponse();

        if(config('api.api_status') === false) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage("We are on maintenance and get back soon!");
            return $apiResponse->outputResponse($apiResponse);
        }

        if($appVersionCode < config('api.app_min_version_support')) {
            $apiResponse->error->setType(config('api.error_type_dialog'));
            $apiResponse->error->setMessage("We no longer support this app version.Kindly upgrade the app from Play store!");
            return $apiResponse->outputResponse($apiResponse);
        }

        if($apiToken != config('api.api_token')) {
            $apiResponse->error->setType(config('api.error_type_toast'));
            $apiResponse->error->setMessage("API token mismatch!");
            return $apiResponse->outputResponse($apiResponse);
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $logFile = 'api.log';

        Log::useDailyFiles(storage_path().'/logs/'.$logFile);


        Log::info('requests', [
            'request_headers' => $request->headers->all(),
            'request' => $request->all(),
            'response' => $response
        ]);
    }
}
