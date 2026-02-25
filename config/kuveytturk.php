<?php

return [
    'merchant_id' => env('KUVEYTTURK_MERCHANT_ID', '496'),
    'customer_id' => env('KUVEYTTURK_CUSTOMER_ID', '400235'),
    'username'    => env('KUVEYTTURK_USERNAME', 'apiuser1'),
    'password'    => env('KUVEYTTURK_PASSWORD', 'api123'),
    'api_url'     => env('KUVEYTTURK_API_URL', 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate'),
    'ok_url'      => env('KUVEYTTURK_OK_URL'),
    'fail_url'    => env('KUVEYTTURK_FAIL_URL'),
];