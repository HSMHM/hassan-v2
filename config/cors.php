<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['POST'],

    'allowed_origins' => [
        'https://api.telegram.org',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Telegram-Bot-Api-Secret-Token',
    ],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
