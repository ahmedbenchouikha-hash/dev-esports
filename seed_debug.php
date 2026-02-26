#!/usr/bin/env php
<?php
// Script d'insertion avec débogage complet

echo "=====================================\n";
echo "  DATABASE SEED SCRIPT\n";
echo "=====================================\n\n";

$dbPath = __DIR__ . '/var/data_dev.db';
echo "[1/3] Connecting to: $dbPath\n";

try {
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ]);
    
    echo "✅ Connected\n\n";
    
    // Step 1: Recompense
    echo "[2/3] Inserting recompense...\n";
    $pdo->exec("DELETE FROM recompense WHERE id = 1");
    echo "  - Cleared old record\n";
    
    $pdo->exec("INSERT INTO recompense (id, recompense, type, classement) 
               VALUES (1, 'OR - Excellence Sportive', 'Premium', 1)");
    echo "  ✅ Recompense inserted\n\n";
    
    // Step 2: Insert demandes
    echo "[3/3] Inserting 6 test cases...\n";
    $pdo->exec("DELETE FROM demande_recompense WHERE id BETWEEN 1 AND 6");
    echo "  - Cleared old records\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO demande_recompense 
        (id, nom_demandeur, email, motif, statut, ai_legitimacy_score, recompense_id, date_demande)
        VALUES (?, ?, ?, ?, ?, ?, 1, datetime('now'))
    ");
    
    $tests = [
        [1, 'Jules Dupont', 'jules@ex.com', 'Championnat régional', 'soumise', 85],
        [2, 'Marie Smith', 'marie@ex.com', 'Bien joué hier', 'pre_analysee', 42],
        [3, 'Tom Spam', 'tom@ex.com', 'give reward', 'rejetee', 15],
        [4, 'Alex Champion', 'alex@ex.com', 'MVP 25/2', 'approuvee', 90],
        [5, 'Lisa Suspicious', 'lisa@ex.com', 'Joué bien', 'rejetee', 25],
        [6, 'Kevin Legend', 'kevin@ex.com', 'Triple penta', 'soumise', 78]
    ];
    
    foreach ($tests as $t) {
        $stmt->execute($t);
        echo "  - Inserted: {$t[1]}\n";
    }
    
    echo "\n✅ SUCCESS!\n";
    echo "  6 test cases loaded into database\n";
    
    // Verify
    $res = $pdo->query("SELECT COUNT(*) cnt FROM demande_recompense");
    $count = $res->fetch()['cnt'];
    echo "\n📊 Verification: $count/6 records\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    exit(1);
}
?>
