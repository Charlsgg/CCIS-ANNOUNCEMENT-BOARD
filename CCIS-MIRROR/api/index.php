<?php

use Illuminate\Http\Request;

// 1. Load Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Boot Application (Laravel 11 style)
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Set Storage to /tmp (Writable for Vercel)
$app->useStoragePath('/tmp/storage');

// 4. Manual Folder Creation
$folders = ['/app/public', '/framework/cache/data', '/framework/sessions', '/framework/views', '/logs'];
foreach ($folders as $f) {
    $path = '/tmp/storage' . $f;
    if (!is_dir($path)) { 
        mkdir($path, 0777, true); 
    }
}

// 5. Execute Request (No Kernel class needed in Laravel 11!)
$request = Request::capture();
$response = $app->handleRequest($request);
$response->send();
$app->terminate();