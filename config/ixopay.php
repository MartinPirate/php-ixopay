<?php

return [
    'username' => env('IXOPAY_USERNAME'),
    'password' => env('IXOPAY_PASSWORD'),
    'api_key' => env('IXOPAY_API_KEY'),
    'shared_secret' => env('IXOPAY_SHARED_SECRET'),
    'language' => env('IXOPAY_LANGUAGE'),
    'gateway_url' => env('IXOPAY_GATEWAY_URL'),
    'custom_request_headers' => [],
    'custom_curl_options' => [],
];
