<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie','local/*','stage/*', 'storage/*', 'live/storage/*'],

    'allowed_methods' => ['*'], // Allow all HTTP methods (GET, POST, etc.)

    'allowed_origins' => ['http://178.128.45.173:9163','http://localhost:3000','https://admin.boxsocials.com','https://boxsocials.com'], // React app origin

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers (Authorization, Content-Type, etc.)

    'exposed_headers' => [], // Headers exposed to the browser (if needed)

    'max_age' => 0, // How long the results of a preflight request can be cached

    'supports_credentials' => true, // Set to true if using cookies/sessions

];
