#!/usr/bin/env php
<?php
// Ultra-simple approach: just insert data into existing schema

$db = new PDO('sqlite:var/data_dev.db', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_TIMEOUT => 30
]);

echo "=[TEST DATA LOADER]=\n\n";

try {
    // 1. Make sure recompense exists
    @$db->exec("INSERT INTO recompense (id, recompense, type, classement) VALUES (1, 'OR - Excellence Sportive','Premium', 1)");
    
   echo "✓ Recompense ready\n";
    
    // 2. Clear test demandes
    $db->exec("DELETE FROM demande_recompense WHERE id BETWEEN 1 AND 6");
    echo "✓ Cleared old test data\n";
    
    // 3. Insert 6 cases
    $sql = "INSERT INTO demande_recompense (id, nom_demandeur, email, motif, statut, ai_legitimacy_score, recompense_id, date_demande) 
            VALUES (?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)";
    $stmt = $db->prepare($sql);
    
    $cases = [
        [1, 'Jules Dupont', 'j@ex.com', 'Championnat régional', 'soumise', 85],
        [2, 'Marie Smith', 'm@ex.com', 'Bien joué hier', 'pre_analysee', 42],
        [3, 'Tom Spam', 't@ex.com', 'give me', 'rejetee', 15],
        [4, 'Alex Champion', 'a@ex.com', 'MVP player', 'approuvee', 90],
        [5, 'Lisa Suspicious', 'l@ex.com', 'Well played', 'rejetee', 25],
        [6, 'Kevin Legend', 'k@ex.com', 'Triple kill', 'soumise', 78],
    ];
    
    foreach ($cases as $c) {
        $stmt->execute($c);
    }
    
    echo "✓ Inserted 6 test cases\n\n";
    
    // Verify
    $r = $db->query("SELECT COUNT(*) c FROM demande_recompense")->fetch();
    echo "Result: {$r['c']}/6 cases in database\n";
    
    if ($r['c'] >= 6) {
        echo "\n✅ SUCCESS!\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR:\n  {$e->getMessage()}\n";
    die(1);
}
?>
