<?php
// Let's see if PHP is actually working
echo "PHP is alive. Version: " . PHP_VERSION;

// Check if files exist
echo "<br>Checking files...";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo " ✅ Vendor found.";
} else {
    echo " ❌ Vendor MISSING.";
}

// Try to load composer and stop
require __DIR__ . '/../vendor/autoload.php';
echo "<br>Composer loaded.";

// If it gets here, the crash happens INSIDE Laravel's boot process
$app = require_once __DIR__ . '/../bootstrap/app.php';
echo "<br>Laravel booted.";