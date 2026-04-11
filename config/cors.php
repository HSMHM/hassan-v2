<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['POST'],

    'allowed_origins' => [
        'https://gate.whapi.cloud',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Webhook-Secret',
    ],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
