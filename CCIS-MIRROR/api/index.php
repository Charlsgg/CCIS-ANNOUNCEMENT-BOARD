<?php

use Illuminate\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

// 1. Create the required directories BEFORE Laravel boots
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

// 2. THE NUCLEAR OPTION: Destroy any cache files generated during Vercel's build step
$defaultCacheDir = __DIR__ . '/../bootstrap/cache';
$filesToNuke = ['services.php', 'packages.php', 'config.php', 'routes.php', 'events.php'];

foreach ($filesToNuke as $file) {
    $badCacheFile = "$defaultCacheDir/$file";
    if (file_exists($badCacheFile)) {
        @unlink($badCacheFile); // Delete the file so Laravel doesn't get confused
    }
}

// 3. Bruteforce ENV variables for Vercel's read-only system
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

// 6. Execute Request with manual Error Catching
try {
    $request = Request::capture();
    $response = $app->handleRequest($request);
    $response->send();
    $app->terminate($request, $response);
} catch (\Throwable $e) {
    // If ANYTHING fails, we bypass Laravel's error handler and print it raw
    http_response_code(500);
    echo "<div style='background:#111; color:#ff5555; padding:20px; font-family:monospace; border-radius:8px;'>";
    echo "<h2>🚨 Vercel Laravel Error!</h2>";
    echo "<b>Message:</b> " . $e->getMessage() . "<br><br>";
    echo "<b>File:</b> " . $e->getFile() . " (Line " . $e->getLine() . ")<br><br>";
    echo "<b>Trace:</b><br><pre style='white-space:pre-wrap; color:#ccc;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    exit;
}