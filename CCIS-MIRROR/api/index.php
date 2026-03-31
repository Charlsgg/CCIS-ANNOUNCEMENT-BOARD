<?php
// 1. Load Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Boot the Laravel App
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. IMPORTANT: Tell Laravel to use Vercel's writable /tmp directory
$app->useStoragePath('/tmp/storage');

// 4. Handle the Request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);