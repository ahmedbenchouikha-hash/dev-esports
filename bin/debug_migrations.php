<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Kernel;

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? true);

$kernel = new Kernel($env, $debug);
$kernel->boot();

$container = $kernel->getContainer();

echo "Container class: " . get_class($container) . PHP_EOL;

if (! $container->has('doctrine.migrations.dependency_factory')) {
    echo "DependencyFactory service not found\n";
    exit(1);
}

$df = $container->get('doctrine.migrations.dependency_factory');
echo "DependencyFactory: " . get_class($df) . PHP_EOL;

try {
    $storage = $df->getMetadataStorage();
    echo "Metadata storage class: " . get_class($storage) . PHP_EOL;

    try {
        $executed = $storage->getExecutedMigrations();
        echo "Executed migrations retrieved: " . count($executed->getItems()) . PHP_EOL;
    } catch (Throwable $e) {
        echo "Error calling getExecutedMigrations(): " . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "Error getting metadata storage: " . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

$kernel->shutdown();
