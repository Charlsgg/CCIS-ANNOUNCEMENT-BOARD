<?php

// 1. Load the Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Boot the Laravel 12 Application
// In Laravel 11/12, this returns a ready-to-use Application object
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Set the storage path to Vercel's writable /tmp directory
// This is critical for session, cache, and view compilation
$app->useStoragePath('/tmp/storage');

// 4. Create the required storage sub-directories if they don't exist
$storageFolders = [
    '/app/public',
    '/framework/cache/data',
    '/framework/sessions',
    '/framework/testing',
    '/framework/views',
    '/logs'
];

foreach ($storageFolders as $folder) {
    $dir = '/tmp/storage' . $folder;
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// 5. Handle the Incoming Request
// Laravel 12 uses handleRequest() for the modern entry point
$app->handleRequest(Illuminate\Http\Request::capture());