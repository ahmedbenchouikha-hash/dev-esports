# Guide de Configuration des Emails - esportdev

## 🔧 Configuration Actuelle

Le projet utilise **Symfony Mailer** avec plusieurs transport possibles.

### Configuration pour Production/Staging (Mailtrap)

Mailtrap est un service SMTP de test professionnel. Pour l'utiliser:

1. **Ouvrez le fichier `.env`** dans la racine du projet
2. **Remplacez la ligne `MAILER_DSN`** par:

```env
MAILER_DSN=smtp://inbound:c99f54dd494991505fbd7839d47c3b32@send.mailtrap.io:587?encryption=tls
```

**Détails de la config:**
- **Host:** `send.mailtrap.io`
- **Port:** `587` (avec TLS)
- **User:** `inbound`
- **Password:** `c99f54dd494991505fbd7839d47c3b32`
- **Domain:** `esprit.tn`

### Configuration pour Développement Local

Pour tester sans connexion internet, modifiez `.env`:

```env
# Option 1: Si Docker est installé
MAILER_DSN=sendmail+docker://default

# Option 2: Si sendmail local est installé
MAILER_DSN=sendmail:///usr/sbin/sendmail -t -i

# Option 3: Simple logging (Linux/Mac)
MAILER_DSN=file:///var/mail
```

**Sur Windows sans Docker**, les emails seront **loggés** dans:
- `var/log/dev.log` (fichier de debug)

## 📧 Test d'Envoi d'Email

Pour tester que la configuration fonctionne:

```bash
php bin/console app:send-mail
```

**Sortie attendue:**
```
[OK] ✓ Email envoyé avec succès via Mailtrap!
[INFO] Vérifiez votre boîte de réception Mailtrap: https://mailtrap.io/
```

## 🌐 Vérifier les Emails Reçus

1. **Allez sur:** https://mailtrap.io/
2. **Connectez-vous** avec votre compte Mailtrap
3. **Vérifiez la boîte de réception** pour voir tous les emails envoyés

## 🚨 Dépannage

### "Connection refused" ou "Host not found"
**Cause:** Pas de connexion internet vers Mailtrap
**Solution:** 
- Vérifier votre connexion internet
- Utiliser une configuration locale (sendmail, Docker, etc.)
- Ou utiliser une VPN si le DNS est filtré

### "The mailer DSN is invalid"
**Cause:** Format de DSN incorrect
**Solution:** Vérifiez que la DSN suit le format exact indiqué ci-dessus

### Emails non reçus mais command ok
**Cause:** Transport asynchrone (Messenger) utilisé par défaut
**Solution:** 
- Les emails sont en queue (dans la BD)
- Lancez le consumer messenger: `php bin/console messenger:consume async`
- Ou passez le transport en mode synchrone dans `config/packages/messenger.yaml`

## 📋 Récapitulatif des Emails Envoyés

### 1. Email de Vérification
**Quand:** Lorsqu'une demande de récompense est créée
**Destinataire:** Email de la personne qui demande
**Contenu:** Lien de vérification avec bouton vert
**Action:** Clic = marquer la demande comme **PRIORITAIRE**

### 2. Email de Changement de Statut
**Quand:** Administrateur change le statut de demande
**Destinataire:** Email du demandeur
**Contenu:** Notification du nouveau statut

### 3. Email de Test
**Commande:** `php bin/console app:send-mail`
**Destinataire:** `ramzi.benhmida@esprit.tn`
**Usage:** Vérifier la configuration du mailer

## 🔐 Sécurité

- ✅ Tokens de vérification: 32 bytes aléatoires en hex
- ✅ Cryptage TLS pour transmission
- ✅ CSRF token sur tous les formulaires
- ✅ Validation emails côté serveur

## 📝 Fichiers Concernés

- `.env` - Configuration de base (MAILER_DSN)
- `config/packages/messenger.yaml` - Transport asynchrone/synchrone
- `src/Service/EmailService.php` - Logique d'envoi
- `src/Command/SendMailCommand.php` - Commande CLI de test
- `templates/demande_recompense/email/` - Templates d'email

---

**Dernière mise à jour:** 2026-02-14  
**Status:** ✅ Operational (en mode test local)
