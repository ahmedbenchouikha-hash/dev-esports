#!/bin/bash
# Script de test des actions sur les demandes de récompenses

echo "=== TEST DES ACTIONS DEMANDE RECOMPENSE ==="
echo ""

# Test 1: Créer 3 demandes de test
echo "✓ Test 1: Créer 3 demandes de test"
php bin/console doctrine:query:sql "INSERT INTO demande_recompense (recompense_id, nom_demandeur, email, motif, date_demande, statut, verification_token, is_prioritaire) 
VALUES 
(1, 'Test User 1', 'test1@example.com', 'Demande de test numero un avec plus de 50 caracteres pour validation', NOW(), 'en_attente', 'token1', 0),
(1, 'Test User 2', 'test2@example.com', 'Demande de test numero deux avec plus de 50 caracteres pour validation', NOW(), 'en_attente', 'token2', 0),
(1, 'Test User 3', 'test3@example.com', 'Demande de test numero trois avec plus de 50 caracteres pour validation', NOW(), 'en_attente', 'token3', 0)"

echo ""

# Test 2: Afficher toutes les demandes
echo "✓ Test 2: Afficher toutes les demandes"
php bin/console doctrine:query:sql "SELECT id, nom_demandeur, email, statut FROM demande_recompense ORDER BY id DESC LIMIT 5"

echo ""

# Test 3: Tests routes
echo "✓ Test 3: Vérification des routes disponibles"
echo "Routes pour demandes de récompenses:"
php bin/console debug:router | grep demande_recompense

echo ""
echo "=== FIN DES TESTS ==="
