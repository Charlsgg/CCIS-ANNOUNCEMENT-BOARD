<?php
// 1. Load Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Boot Laravel 12
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Setup Vercel-friendly storage
$app->useStoragePath('/tmp/storage');

$storageFolders = [
    '/app/public',
    '/framework/cache/data',
    '/framework/sessions',
    '/framework/views',
    '/logs'
];

foreach ($storageFolders as $folder) {
    $dir = '/tmp/storage' . $folder;
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// 4. FIX: Handle SQLite on Vercel
$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    touch($dbPath); // Create an empty database file in the writable /tmp
}

// 5. Run the App
$app->handleRequest(Illuminate\Http\Request::capture());