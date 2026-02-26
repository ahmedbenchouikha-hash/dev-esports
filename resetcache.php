<?php
// Force reset cache cache
$cacheDir = __DIR__ . '/var/cache/dev';
$logDir = __DIR__ . '/var/log';

// Create directories
@mkdir($cacheDir, 0777, true);
@mkdir($logDir, 0777, true);

// Clear files
$files = array_merge(
    glob($cacheDir . '/*'),
    glob($logDir . '/*')
);

foreach ($files as $file) {
    if (is_file($file)) {
        @unlink($file);
    } elseif (is_dir($file)) {
        array_map('unlink', glob($file . '/*'));
        @rmdir($file);
    }
}

// Create dummy files
file_put_contents($logDir . '/dev.log', date('Y-m-d H:i:s') . " Cache reset\n");
file_put_contents($cacheDir . '/.gitkeep', '');

echo "✓ Cache cleared manually\n";
?>
