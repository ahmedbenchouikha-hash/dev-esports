<?php
// Load corrected database seed data
$conn = mysqli_connect('localhost', 'root', '', 'esportdevvvvvv');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Disable foreign key checks during loading
mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=0');

// Read the SQL file
$sql = file_get_contents(__DIR__ . '/database_seed.sql');

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));

$count = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos(trim($statement), '--') === 0) {
        continue;
    }
    
    if (mysqli_query($conn, $statement . ';')) {
        $count++;
        echo ".";
    } else {
        $errors++;
        echo "\n[ERROR] " . mysqli_error($conn) . "\nStatement: " . substr($statement, 0, 100) . "...\n";
    }
}

// Re-enable foreign key checks
mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');

mysqli_close($conn);

echo "\n\nLoaded $count SQL statements successfully with $errors errors!\n";
?>
