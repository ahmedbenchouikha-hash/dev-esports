# Configuration du Mailer pour DemandeRecompense

## Configuration Actuelle : Mailtrap ✅

Le projet est actuellement configuré pour utiliser **Mailtrap** avec le domaine **esprit.tn**.

### Informations de Connexion

- **API Token** : `c99f54dd494991505fbd7839d47c3b32`
- **Domaine** : `esprit.tn`
- **Service SMTP** : `send.mailtrap.io:2525`
- **Adresse d'expédition** : `hello@esprit.tn`

### Configuration .env

```dotenv
MAILER_DSN=smtp://inbound:c99f54dd494991505fbd7839d47c3b32@send.mailtrap.io:2525?encryption=tls
```

---

## 📧 Workflow d'Email

### 1. **Création de Demande**
- L'utilisateur crée une demande de récompense
- Un email de **vérification** est envoyé à son adresse
- Email contient un lien de confirmation unique avec un token

### 2. **Vérification Email**
- L'utilisateur clique sur le lien de vérification
- Son email est marqué comme vérifié
- Il reçoit le message : **"Votre email a été vérifié avec succès ! Attendez le résultat de votre demande par l'administrateur dans quelques heures."**

### 3. **Changement de Statut**
- L'administrateur change le statut de la demande (approuvée/rejetée/en attente)
- Un email de notification est envoyé au demandeur avec le nouveau statut

---

## 🧪 Tester l'Envoi d'Email

### Commande de Test

```bash
php bin/console app:send-mail
```

Cette commande envoie un email de test à `ramzi.benhmida@esprit.tn` et vous confirme l'envoi.

### Vérifier les Emails Reçus

1. Accédez à votre inbox Mailtrap : https://mailtrap.io/
2. Connectez-vous avec votre compte GitHub
3. Vous verrez tous les emails envoyés par l'application

---

## 🏗️ Architecture des Emails

### Service EmailService

Localisation : `src/Service/EmailService.php`

**Méthodes principales** :
- `sendVerificationEmail(DemandeRecompense)` - Envoie l'email de vérification
- `sendStatusChangeEmail(DemandeRecompense, string)` - Envoie notification de statut
- `sendTestEmail(string)` - Envoie un email de test

### Templates d'Email

Localisation : `templates/demande_recompense/email/`

- `verification.html.twig` - Email de vérification avec lien de confirmation
- `status_change.html.twig` - Email de notification de changement de statut

### Contrôleur

Localisation : `src/Controller/DemandeRecompenseController.php`

**Utilise automatiquement les emails** :
- Lors de la création d'une demande
- Lors de la vérification d'email
- Lors du changement de statut par l'admin

---

## 🔧 Si vous voulez changer de Fournisseur d'Email

### Option 1 : Gmail

```dotenv
MAILER_DSN=gmail+smtp://votremail@gmail.com:votremotdepasse@default
```

### Option 2 : Autre serveur SMTP

```dotenv
MAILER_DSN=smtp://utilisateur:motdepasse@serveur.com:587?encryption=tls
```

### Option 3 : Mode Test (Emails dans console)

```dotenv
MAILER_DSN=null://null
```

---

## ✨ Fonctionnalités Implémentées

- ✅ Génération automatique de tokens de vérification
- ✅ Envoi d'email de vérification
- ✅ Page de vérification email sécurisée
- ✅ Emails de notification de statut
- ✅ Service centralisé pour la gestion des emails
- ✅ Gestion des erreurs d'envoi
- ✅ Logging des erreurs d'email
- ✅ Commande de test d'email

---

## 📊 Tokens de Vérification

- **Génération** : Automatique lors de la création (32 bytes hex)
- **Stockage** : Base de données (`verification_token`)
- **Expiration** : Pas d'expiration actuellement (à ajouter si nécessaire)
- **Utilisation** : URL de vérification unique

### Exemple de Token

```
c99f54dd494991505fbd7839d47c3b32e12f3456789abcdef0123456789abcd
```

---

## 🚀 Prochaines Étapes

1. **Tester la création de demande** : Créez une demande via l'interface web
2. **Vérifier l'email** : Accédez à Mailtrap et vérifiez l'email de vérification
3. **Cliquer le lien** : Cliquez sur le lien de vérification dans l'email
4. **Admin change le statut** : Changez le statut et vérifiez le nouvel email

---

## 📧 Support

Pour toute question sur Mailtrap :
- Site officiel : https://mailtrap.io/
- Documentation : https://mailtrap.io/blog/
- Support Mailtrap : https://support.mailtrap.io/

