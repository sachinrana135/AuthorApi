<?php

return [

    /*
   |--------------------------------------------------------------------------
   | API Configuration
   |--------------------------------------------------------------------------
   */

    'api_status' => 'true',

    'api_token' => env('API_TOKEN', 'Your API Token'),

    'app_min_version_support' => env('APP_MIN_VERSION_SUPPORT', 'Minimum app version supported'),

    'error_type_toast' => 'toast',

    'error_type_dialog' => 'dialog',
    
    'target_type_single' => 'single',
    
    'target_type_topic' => 'topic',
    
    'push_type_default' => 'default',
    
    'push_type_quote' => 'quote',
    
    'push_type_author' => 'author',
    
    'push_type_comment' => 'comment',
    

];