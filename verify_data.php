<?php
$pdo = new PDO('sqlite:var/data_dev.db');
$res = $pdo->query('SELECT COUNT(*) as cnt FROM demande_recompense');
$row = $res->fetch(PDO::FETCH_ASSOC);
$count = $row['cnt'] ?? 0;

echo "Demandes en BD: $count/6" . PHP_EOL;

if ($count >= 6) {
    echo "✅ SUCCESS! All 6 test cases loaded." . PHP_EOL;
} elseif ($count > 0) {
    echo "⚠️  Partial: $count records loaded" . PHP_EOL;
} else {
    echo "❌ No data found" . PHP_EOL;
}
?>
