<?php
$pdo = new PDO('mysql:host=localhost;dbname=esportdevvvvvv', 'root', '');
try {
    $pdo->exec("INSERT IGNORE INTO doctrine_migration_versions (version, executed_at) VALUES ('DoctrineMigrations\\\\Version20260218134603', NOW())");
    echo "✓ Migration marked as executed\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
