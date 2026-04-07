<?php

use Illuminate\Http\Request;

// 1. Load Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Create the required directories BEFORE Laravel boots
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

// 3. CRITICAL: Override bootstrap/cache paths to use the writable /tmp directory
$tmpBootstrapCache = '/tmp/storage/bootstrap/cache';
putenv("APP_SERVICES_CACHE={$tmpBootstrapCache}/services.php");
putenv("APP_PACKAGES_CACHE={$tmpBootstrapCache}/packages.php");
putenv("APP_CONFIG_CACHE={$tmpBootstrapCache}/config.php");
putenv("APP_ROUTES_CACHE={$tmpBootstrapCache}/routes.php");
putenv("APP_EVENTS_CACHE={$tmpBootstrapCache}/events.php");

// 4. Boot Application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 5. Force Laravel to use the writable /tmp directory for standard storage
$app->useStoragePath('/tmp/storage');

// 6. Execute Request
$app->handleRequest(Request::capture());