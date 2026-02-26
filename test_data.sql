-- Ajouter plus de budgets
INSERT IGNORE INTO budget (id, montant_alloue, montant_utilise, team_id, date_allocation, statut) VALUES
(2, 1500, 900, 2, NOW(), 'actif'),
(3, 2000, 1200, 3, NOW(), 'actif'),
(4, 1800, 1500, 4, NOW(), 'actif'),
(5, 2500, 500, 5, NOW(), 'actif');

-- Ajouter plus de dépenses
INSERT IGNORE INTO depense (titre, description, montant, categorie, statut, date_creation, team_id) VALUES
('PC Gaming', 'Ordinateur pour joueur', 800, 'materiel', 'validée', NOW(), 2),
('Clavier Mécanique', 'Clavier professionnel', 150, 'materiel', 'validée', NOW(), 2),
('Transport tournoi', 'Frais transport équipe', 200, 'transport', 'validée', NOW(), 2),
('Repas équipe', 'Restauration joueurs', 100, 'nourriture', 'validée', NOW(), 3),
('Hébergement', 'Hôtel tournoi', 500, 'logistique', 'validée', NOW(), 3),
('Moniteur 27', 'Écran haute résolution', 400, 'materiel', 'validée', NOW(), 4),
('Souris gaming', 'Souris professionnelle', 80, 'materiel', 'validée', NOW(), 5),
('Casque audio', 'Casque communication', 120, 'materiel', 'validée', NOW(), 5),
('Licence logiciel', 'Logiciel gaming', 250, 'logistique', 'en_attente', NOW(), 1);
