INSERT INTO demande_recompense (recompense_id, nom_demandeur, email, motif, date_demande, statut, verification_token, is_prioritaire) 
VALUES 
(1, 'Alice Johnson', 'alice.johnson@example.com', 'Je demande cette recompense car j ai remporte plusieurs matchs importants et maintenu une excellente performance.', NOW(), 'en_attente', 'token_alice_001', 0),
(1, 'Bob Davidson', 'bob.davidson@example.com', 'Demande de recompense pour participation active aux tournois et contribution a l equipe tout au long de la saison.', NOW(), 'approuvee', 'token_bob_002', 1),
(1, 'Charlie Brown', 'charlie.brown@example.com', 'Je demande cette recompense pour reconnaissance de mon engagement dans les competitions esports cette annee.', NOW(), 'en_attente', 'token_charlie_003', 0),
(1, 'Diana Prince', 'diana.prince@example.com', 'Demande de recompense suite a ma victoire dans le dernier tournoi regional avec un score impressionnant.', NOW(), 'rejetee', 'token_diana_004', 0),
(1, 'Eric White', 'eric.white@example.com', 'Je demande cette recompense pour reconnaître mes efforts constants et ma dedication aux competitions esports.', NOW(), 'en_attente', 'token_eric_005', 0);
