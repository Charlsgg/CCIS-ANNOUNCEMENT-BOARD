<?php

use Illuminate\Http\Request;

// 1. Load Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Create the required directories BEFORE Laravel boots
// This is critical. If these don't exist when Laravel loads its config, it will crash.
$directories = [
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/storage/bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 3. Boot Application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 4. Force Laravel to use the writable /tmp directory
$app->useStoragePath('/tmp/storage');

// 5. Execute Request (Laravel 11 natively handles send() and terminate() here)
$app->handleRequest(Request::capture());