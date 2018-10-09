<?php

/**
 * Created by PhpStorm.
 * User: srana
 * Date: 8/24/2017
 * Time: 11:59 AM
 */

namespace App;

use App;

class ApiResponse {

    public $config = '';
    public $response = '';
    public $error = '';

    /**
     * ApiResponse constructor.
     */
    public function __construct() {
        $this->config = new ApiConfig();
        $this->error = new ApiError();
    }

    /**
     * @return mixed
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response) {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error) {
        $this->error = $error;
    }

    public function outputResponse(ApiResponse $apiResponse) {
        // Set Config Values
        if ($appVersionCode <= 9) {
            $apiResponse->config->setApiStatus(config('api.api_status'));
            $apiResponse->config->setMinSupportVersion(config('api.app_min_version_support'));
        }
        return response()->json($apiResponse);
    }

}

class ApiConfig {

    public $apiStatus = '';
    public $minSupportVersion = '';

    /**
     * @return mixed
     */
    public function getApiStatus() {
        return $this->apiStatus;
    }

    /**
     * @param mixed $apiStatus
     */
    public function setApiStatus($apiStatus) {
        $this->apiStatus = $apiStatus;
    }

    /**
     * @return mixed
     */
    public function getMinSupportVersion() {
        return $this->minSupportVersion;
    }

    /**
     * @param mixed $minSupportVersion
     */
    public function setMinSupportVersion($minSupportVersion) {
        $this->minSupportVersion = $minSupportVersion;
    }

}

class ApiError {

    public $type = '';
    public $message = '';

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

}
