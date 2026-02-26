INSERT INTO demande_recompense (recompense_id, nom_demandeur, email, motif, date_demande, statut, verification_token, is_prioritaire) 
VALUES (1, 'Alice Martin', 'alice@example.com', 'Je demande cette recompense car j ai beaucoup participe aux tournois', NOW(), 'en_attente', 'token456', 0);

INSERT INTO demande_recompense (recompense_id, nom_demandeur, email, motif, date_demande, statut, verification_token, is_prioritaire) 
VALUES (1, 'Bob Durand', 'bob@example.com', 'Je demande cette recompense car j ai remporte plusieurs matchs importants', NOW(), 'en_attente', 'token789', 0);
