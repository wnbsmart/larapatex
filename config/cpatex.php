<?php
return [
    /*
    |--------------------------------------------------------------------------
    | C-PatEx authentication
    |--------------------------------------------------------------------------
    |
    | Authentication key and secret for C-PatEx API.
    |
     */
    'auth' => [
        'access_key'    => env('CPATEX_ACCESS_KEY', ''),
        'secret_key' => env('CPATEX_SECRET_KEY', ''),
    ],
    /*
    |--------------------------------------------------------------------------
    | Api URLS
    |--------------------------------------------------------------------------
    |
    | C-PatEx API endpoints
    |
     */
    'urls' => [
        'api'  => 'https://c-patex.com/api/'
    ],
];