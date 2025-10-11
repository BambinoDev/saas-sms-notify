<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CommCare Domain
    |--------------------------------------------------------------------------
    |
    | Your CommCare project domain (e.g., 'my-project')
    |
    */
    'domain' => env('COMMCARE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | CommCare Credentials
    |--------------------------------------------------------------------------
    |
    | API username and password for authentication
    |
    */
    'username' => env('COMMCARE_USERNAME'),
    'password' => env('COMMCARE_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | CommCare API URL
    |--------------------------------------------------------------------------
    |
    | Base URL for CommCare API. Use {domain} placeholder.
    |
    */
    'api_url' => env('COMMCARE_API_URL', 'https://www.commcarehq.org/a/{domain}/api/v0.5'),

    /*
    |--------------------------------------------------------------------------
    | Case Type
    |--------------------------------------------------------------------------
    |
    | The type of cases to sync (e.g., 'patient', 'mother', 'child')
    |
    */
    'case_type' => env('COMMCARE_CASE_TYPE', 'patient'),

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    |
    | Number of cases to fetch per API request
    |
    */
    'batch_size' => env('COMMCARE_BATCH_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | API Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum seconds to wait for API response
    |
    */
    'timeout' => env('COMMCARE_TIMEOUT', 30),

];

