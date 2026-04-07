<?php

use Illuminate\Http\Request;

// 1. Load Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Create the required directories BEFORE Laravel boots
$tmpDir = '/tmp/storage';
$directories = [
    "$tmpDir/app/public",
    "$tmpDir/framework/cache/data",
    "$tmpDir/framework/sessions",
    "$tmpDir/framework/views",
    "$tmpDir/logs",
    "$tmpDir/bootstrap/cache"
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 3. Bruteforce ENV variables for Vercel's read-only system
// We set putenv, $_ENV, and $_SERVER to ensure Laravel's env() helper catches it
$overrides = [
    'VIEW_COMPILED_PATH' => "$tmpDir/framework/views",
    'APP_SERVICES_CACHE' => "$tmpDir/bootstrap/cache/services.php",
    'APP_PACKAGES_CACHE' => "$tmpDir/bootstrap/cache/packages.php",
    'APP_CONFIG_CACHE'   => "$tmpDir/bootstrap/cache/config.php",
    'APP_ROUTES_CACHE'   => "$tmpDir/bootstrap/cache/routes.php",
    'APP_EVENTS_CACHE'   => "$tmpDir/bootstrap/cache/events.php",
];

foreach ($overrides as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

// 4. Boot Application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 5. Force Laravel to use the writable /tmp directory
$app->useStoragePath($tmpDir);

// 6. Execute Request
$app->handleRequest(Request::capture());