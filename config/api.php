<?php

return [

    /*
   |--------------------------------------------------------------------------
   | API Configuration
   |--------------------------------------------------------------------------
   */

    'api_status' => env('API_STATUS', 'Set false for maintainence'),

    'api_token' => env('API_TOKEN', 'Add your API Token'),

    'app_min_version_support' => env('APP_MIN_VERSION_SUPPORT', 'Minimum app version supported by the API'),
    
    'app_live_version_code' => env('APP_LIVE_VERSION_CODE', 'Set current app live version number here'),

    'error_type_toast' => 'toast',

    'error_type_dialog' => 'dialog',
    
    'target_type_single' => 'single',
    
    'target_type_topic' => 'topic',
    
    'push_type_default' => 'default',
    
    'push_type_quote' => 'quote',
    
    'push_type_author' => 'author',
    
    'push_type_comment' => 'comment',
    
    'push_type_app_upgrade' => 'app_upgrade',
    

];