-- Migration pour VichUploaderBundle
-- Ajouter colonnes pour les fichiers

-- Table TEAM (Logo)
ALTER TABLE team ADD COLUMN logo_file VARCHAR(255) NULL COMMENT 'Fichier logo équipe';

-- Table DEPENSE (Facture)
ALTER TABLE depense ADD COLUMN facture_file VARCHAR(255) NULL COMMENT 'Fichier facture dépense';

-- Table BUDGET (Justificatif)
ALTER TABLE budget ADD COLUMN justificatif_file VARCHAR(255) NULL COMMENT 'Fichier justificatif budget';
