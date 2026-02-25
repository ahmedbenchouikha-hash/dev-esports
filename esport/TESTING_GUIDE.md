# 🧪 Guide Complet de Test - Système De Demande de Récompense

## ✅ Configuration Actuelle

- **Mailer** : Mailtrap (esprit.tn)
- **Base de données** : MySQL
- **Serveur** : Symfony Serve HTTPS

---

## 🚀 Étapes de Test Complet

### 1️⃣ **Vérifier la Connexion Mailtrap**

```bash
php bin/console app:send-mail
```

**Résultat attendu** :
```
✓ Email envoyé avec succès via Mailtrap!
Vérifiez votre boîte de réception Mailtrap : https://mailtrap.io/
```

✅ Accédez à https://mailtrap.io/ et vérifiez l'email de test reçu.

---

### 2️⃣ **Créer une Demande de Récompense**

1. Accédez à : `https://127.0.0.1:8000/demande-recompense`
2. Cliquez sur **"Nouvelle Demande"**
3. Remplissez le formulaire :
   - **Récompense** : Sélectionnez une récompense
   - **Nom** : Votre nom
   - **Email** : Votre adresse email
   - **Motif** : Un motif de demande
4. Cliquez **"Soumettre la Demande"**

**Message attendu** :
```
✓ Demande créée avec succès ! Veuillez vérifier votre email pour confirmer votre adresse.
```

---

### 3️⃣ **Vérifier l'Email de Vérification**

1. Accédez à : https://mailtrap.io/
2. Vous devriez voir un nouvel email avec le sujet : **"Vérifiez votre adresse email - Demande de récompense"**
3. Ouvrez l'email
4. Recherchez le bouton **"Vérifier mon email"** ou le lien dans le corps du message

**Contenu attendu** :
- Récapitulatif de la demande
- Récompense demandée
- Date de soumission
- Lien de vérification unique

---

### 4️⃣ **Vérifier l'Email**

**Option A : Via le bouton de l'email**
1. Cliquez sur **"Vérifier mon email"** dans l'email Mailtrap

**Option B : Via le lien direct**
1. Copiez le lien de vérification depuis l'email
2. Ouvrez-le dans votre navigateur

**Rendu page** :
```
✓ Votre email a été vérifié avec succès ! Attendez le résultat de votre demande par l'administrateur dans quelques heures.
```

---

### 5️⃣ **Vérifier le Statut de la Demande**

1. Allez à : `https://127.0.0.1:8000/demande-recompense` (liste)
2. Cliquez sur votre demande pour voir les détails
3. Vous devriez voir un **bandeau vert** indiquant que l'email est vérifié

---

### 6️⃣ **Simuler une Réponse de l'Admin**

1. Sur la page de détails de la demande, allez à la section **"Statut"**
2. Cliquez sur l'un des boutons :
   - ✓ **Approuvée**
   - ✗ **Rejetée**
   - ◎ **En Attente**

**Message attendu** :
```
✓ Statut de la demande mis à jour et email envoyé.
```

---

### 7️⃣ **Vérifier l'Email de Notification**

1. Accédez à : https://mailtrap.io/
2. Vous devriez voir un nouvel email avec le sujet : **"Mise à jour du statut de votre demande de récompense"**
3. L'email devrait contenir :
   - Le nouveau statut (Approuvée/Rejetée/En Attente)
   - Les détails de la demande
   - ID de la demande

---

## 📊 Checklist de Vérification

- [ ] Commande `app:send-mail` fonctionne
- [ ] Email de test reçu sur Mailtrap
- [ ] Création de demande accessible
- [ ] Formulaire de demande valide
- [ ] Email de vérification reçu
- [ ] Lien de vérification fonctionne
- [ ] Page de confirmation affiche le bon message
- [ ] Statut de la demande passe à "Approuvé"
- [ ] Email de notification reçu
- [ ] Tous les détails sont corrects dans les emails

---

## 🎨 Fonctionnalités Supplémentaires

### Liste des Demandes
- ✅ Recherche par nom, email, récompense, statut
- ✅ Tri par :
  - Date (récent/ancien)
  - Nom (A-Z)
  - Statut
- ✅ Affichage du statut avec couleur
- ✅ Boutons d'action (voir, éditer, supprimer)

### Détails de la Demande
- ✅ Affiche toutes les informations
- ✅ Indicateur de vérification d'email
- ✅ Section de changement de statut
- ✅ Description de la récompense

### Édition de Demande
- ✅ Modification de tous les champs
- ✅ Validation complète
- ✅ Messages de succès/erreur

---

## 🔧 Dépannage

### Email ne s'envoie pas
```bash
# Vérifiez la configuration MAILER_DSN
grep MAILER_DSN .env

# Testez manuellement
php bin/console app:send-mail

# Vérifiez les logs
tail -f var/log/dev.log
```

### Lien de vérification ne fonctionne pas
1. Vérifiez que l'URL est correcte dans l'email
2. Vérifiez que le token est stocké dans la base de données
3. Vérifiez que le domaine HTTPS est correct

### Formulaire refuse la soumission
1. Vérifiez les erreurs de validation affichées
2. Assurez-vous que :
   - Une récompense est sélectionnée
   - Le nom n'est pas vide
   - L'email est valide
   - L'email n'a pas dû remplacé

---

## 📚 Fichiers Importants

- **Configuration** : `.env`
- **Service Email** : `src/Service/EmailService.php`
- **Contrôleur** : `src/Controller/DemandeRecompenseController.php`
- **Entité** : `src/Entity/DemandeRecompense.php`
- **Templates Email** : `templates/demande_recompense/email/`
- **Commande Test** : `src/Command/SendMailCommand.php`

---

## 💡 Prochaines Améliorations Possibles

- [ ] Expiration des tokens de vérification (24h)
- [ ] Renvoi d'email de vérification
- [ ] Notification admin lors de nouvelle demande
- [ ] Historique des changements de statut
- [ ] Pièce jointe dans les emails
- [ ] Export PDF des demandes
- [ ] Dashboard statistiques

---

## ✨ Notes

- Les emails sont envoyés de manière **synchrone** lors de l'action
- Tous les emails ont le domaine `hello@esprit.tn`
- Les tokens de vérification sont **uniques** par demande
- Une fois vérifié, le token est **supprimé** de la BD

---

**Besoin d'aide ?** Consultez [MAILER_CONFIG.md](MAILER_CONFIG.md) pour les détails de configuration.
