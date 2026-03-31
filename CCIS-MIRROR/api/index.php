<?php
// 1. Load Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Boot the Laravel App
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Set the storage path to Vercel's writable /tmp directory
$app->useStoragePath('/tmp/storage');

// 4. IMPORTANT: Create the required storage directories dynamically
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
        mkdir($dir, 0777, true); // Create the folder if it doesn't exist
    }
}

// 5. Handle the Request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);