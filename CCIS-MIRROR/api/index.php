<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Vercel PHP Health Check</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";

$paths = [
    'Vendor Autoload' => __DIR__ . '/../vendor/autoload.php',
    'Bootstrap App'   => __DIR__ . '/../bootstrap/app.php',
    'Environment'     => __DIR__ . '/../.env'
];

foreach ($paths as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name found.<br>";
    } else {
        echo "❌ $name NOT FOUND at: $path<br>";
    }
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    echo "✅ Composer loaded successfully!<br>";
}