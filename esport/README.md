# Module Gestion des Récompenses (Rewards Management)

## Description
Module de gestion des demandes de récompenses pour la plateforme esports avec intégration IA (Mistral AI) pour l'analyse automatique et la génération de motifs.

## Fonctionnalités

### 1. Gestion des Demandes de Récompenses
- **CRUD des demandes** : Création, lecture, mise à jour et suppression
- **Validation multi-niveaux** : PHP server-side avec messages en anglais
- **Liste paginée** avec recherche et tri
- **Filtrage par statut** : En attente, Approuvée, Rejetée
- **Système de priorité** : Low (par défaut) → High (après vérification email)

### 2. Intelligence Artificielle (Mistral AI)
- **Génération de motifs** :
  - Si motif vide : génère un nouveau motif à la 1ère personne
  - Si motif existant : améliore et développe le texte
- **Analyse automatique** :
  - Score de légitimité (0-100)
  - Détection de fraude
  - Extraction de points clés
  - Analyse de sentiment
  - Suggestions de type de récompense

### 3. Système d'Email (SendGrid)
- **Email de vérification** : Envoyé à l'adresse saisie dans le formulaire
- **Email de confirmation** : Confirmation de création de demande
- **Email de changement de statut** : Notification admin approve/reject
- **Lien de vérification sécurisé** : Token unique par demande

### 4. Contrôle d'Accès
- **Admin** :
  - Vue de toutes les demandes
  - Approbation/Rejet des demandes
  - Gestion complète des récompenses (CRUD)
- **Joueur** :
  - Création de demandes (limite : 3 par email)
  - Vue de ses propres demandes uniquement
  - Modification/Suppression de ses demandes
  - Saisie libre de l'email (peut différer du compte)

### 5. Priorité Automatique
- **Low** : Par défaut à la création
- **High** : Automatiquement après vérification de l'email
- Badge visuel dans la liste (gris/rouge)

## Fichiers Principaux

### Backend
- `src/Controller/DemandeRecompenseController.php` - Contrôleur principal
- `src/Entity/DemandeRecompense.php` - Entité avec champ `createdByEmail`
- `src/Repository/DemandeRecompenseRepository.php` - Repository avec filtres
- `src/Service/AIRewardAnalysisService.php` - Intégration Mistral AI
- `src/Service/EmailService.php` - Service d'envoi d'emails
- `src/DTO/RewardAnalysisDTO.php` - DTO pour résultats IA
- `src/Form/DemandeRecompenseType.php` - Formulaire Symfony

### Frontend
- `templates/demande_recompense/list.html.twig` - Liste avec modal AJAX
- `templates/demande_recompense/show.html.twig` - Détails + analyse IA
- `templates/demande_recompense/new.html.twig` - Formulaire full-page
- `templates/demande_recompense/_form_modal.html.twig` - Formulaire modal
- `templates/demande_recompense/email/verification.html.twig` - Template email vérification

### Configuration
- `config/services.yaml` - Services et paramètres Mistral AI
- `config/packages/vich_uploader.yaml` - Configuration uploads
- `config/packages/mailer.yaml` - Configuration SendGrid

### Migrations
- Création table demande_recompense
- Ajout champs IA (ai_legitimacy_score, ai_fraud_type, etc.)
- Ajout champ `created_by_email` pour ownership

## Variables d'Environnement

```env
# Mistral AI
MISTRAL_API_KEY=your_mistral_api_key_here

# SendGrid
SENDGRID_API_KEY=your_sendgrid_api_key_here
MAILER_DSN=sendgrid://default@default
```

## Installation

1. Copier les fichiers du module dans votre projet Symfony
2. Configurer les variables d'environnement dans `.env.local`
3. Exécuter les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
4. Vider le cache :
   ```bash
   php bin/console cache:clear
   ```

## Routes Principales

- `/demande-recompense` - Liste des demandes (paginée)
- `/demande-recompense/new` - Création demande (full-page)
- `/demande-recompense/{id}` - Détails d'une demande
- `/demande-recompense/{id}/verify/{token}` - Vérification email
- `/demande-recompense/modal/form` - Formulaire modal AJAX
- `/demande-recompense/motif-suggest` - Endpoint suggestion IA
- `/demande-recompense/{id}/statut` - Changement statut (admin)

## API Externes

### Mistral AI
- **Modèle** : `mistral-small`
- **Endpoint** : `https://api.mistral.ai/v1/chat/completions`
- **Usage** :
  - Génération de motifs personnalisés
  - Analyse de légitimité des demandes
  - Détection de fraude

### SendGrid
- **Service** : Email transactionnel
- **From Email** : `ramzi.benhmida@esprit.tn`
- **Templates** : HTML Twig personnalisés

## Workflow Utilisateur

1. Joueur remplit le formulaire (récompense + nom + email + motif optionnel)
2. Clique "AI Suggest" pour générer/améliorer le motif
3. Soumet la demande → Status "Low"
4. Reçoit email de vérification dans sa boîte mail
5. Clique sur le lien de confirmation
6. Status passe à "High" automatiquement
7. Admin voit la demande et l'analyse IA
8. Admin approuve/rejette → Email de notification envoyé

## Sécurité

- Validation CSRF sur tous les formulaires
- Contrôle d'accès par rôle (ROLE_ADMIN / ROLE_USER)
- Filtrage par ownership (`createdByEmail`)
- Token de vérification unique et sécurisé
- Validation email avec regex stricte
- Limite de 3 demandes par email

## Technologies

- **Backend** : Symfony 7.x, PHP 8.2+
- **Base de données** : MySQL 8.0
- **AI** : Mistral AI API
- **Email** : SendGrid
- **Frontend** : Bootstrap 5, JavaScript Vanilla
- **Pagination** : KnpPaginatorBundle

## Auteur

Module développé pour la plateforme Dev Esports - Gestion des Récompenses

## Dépendances

```json
{
  "symfony/form": "^7.0",
  "symfony/validator": "^7.0",
  "symfony/mailer": "^7.0",
  "symfony/http-client": "^7.0",
  "doctrine/orm": "^3.0",
  "knplabs/knp-paginator-bundle": "^6.0",
  "vich/uploader-bundle": "^2.0"
}
```
