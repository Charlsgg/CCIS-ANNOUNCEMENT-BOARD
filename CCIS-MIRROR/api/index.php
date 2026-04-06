<?php

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;

// 1. Load Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Boot Application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Set Storage to /tmp (Writable)
// This is required because Vercel's root filesystem is read-only
$app->useStoragePath('/tmp/storage');

// 4. Manual Folder Creation 
$folders = ['/app/public', '/framework/cache/data', '/framework/sessions', '/framework/views', '/logs'];
foreach ($folders as $f) {
    $path = '/tmp/storage' . $f;
    if (!is_dir($path)) { 
        mkdir($path, 0777, true); 
    }
}

// 5. Setup the Kernel
$kernel = $app->make(Kernel::class);

// 6. Initialize Core Services (Bypasses "Class not found" errors)
$kernel->bootstrap();

// 7. Execute Request
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);