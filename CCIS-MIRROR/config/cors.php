<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'], 

    'allowed_methods' => ['*'],

    // 1. Add your exact local URLs here
    'allowed_origins' => [
        'http://localhost:8000', 
        'http://127.0.0.1:8000',
        'http://localhost:5173', 
        'http://127.0.0.1:5173'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // 2. Change this to true!
    'supports_credentials' => true, 
];