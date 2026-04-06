<?php

use Illuminate\Http\Request;

// 1. Force PHP to show errors! (CRITICAL FOR VERCEL DEBUGGING)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 2. Load Composer
require __DIR__ . '/../vendor/autoload.php';

// 3. Boot Application (Laravel 11 style)
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 4. Set Storage to /tmp (Writable for Vercel)
$app->useStoragePath('/tmp/storage');

// 5. Manual Folder Creation
$folders = [
    '/app/public', 
    '/framework/cache/data', 
    '/framework/sessions', 
    '/framework/views', 
    '/logs'
];

foreach ($folders as $f) {
    $path = '/tmp/storage' . $f;
    if (!is_dir($path)) { 
        mkdir($path, 0777, true); 
    }
}

// 6. Execute Request (Laravel 11 natively handles send() and terminate() inside this method)
$app->handleRequest(Request::capture());