# 🚀 DÉMARRAGE RAPIDE - Test du Système Priorité

## ⚡ Test en 5 Minutes

### 1. Démarrer Symfony
```bash
cd c:\Users\F\OneDrive\Bureau\esportdev-nouv\esport
php bin/console cache:clear
symfony serve
```
L'app est accessible: **https://127.0.0.1:8000**

### 2. Créer une Demande
1. Allez sur: https://127.0.0.1:8000/demandes/new
2. Remplissez:
   - Récompense: (choisir une)
   - Nom: "Ali Test"
   - Email: "ali@test.local"
   - Motif: "Je veux tester"
3. Cliquez "Soumettre"

✓ **Résultat attendu:** 
- Redirection vers la demande
- Email "envoyé" (loggé localement)

### 3. Vérifier Email Génération
Ouvrez `var/log/dev.log`:
```bash
tail -f var/log/dev.log | grep -i email
```

Vous devriez voir:
```
✓ Email de vérification envoyé à: ali@test.local
```

### 4. Tester Vérification Email
Vous pouvez trouver l'URL de vérification ainsi:
```bash
grep -o "verify-email/[^\"]*" var/log/dev.log
```

Acc

édez l'URL directement:
```
https://127.0.0.1:8000/demandes/{id}/verify-email/{token}
```

✓ **Résultat:**
- Message: "Demande marquée comme prioritaire"
- Redirection vers détails

### 5. Voir le Badge Prioritaire
Allez sur: https://127.0.0.1:8000/demandes

✓ **Vous devriez voir:**
- Colonne "Priorité" avec badge ⭐ **PRIORITAIRE**
- Clic sur la demande → voir "Priorité" en détail

---

## 📧 Configuration Email (Important!)

### Actuellement (Dev Local)
```env
MAILER_DSN=smtp://localhost:1025
```
→ Les emails sont loggés, pas vraiment envoyés

### Pour Production (Mailtrap)
Ouvrez `.env` et changez:
```env
MAILER_DSN=smtp://inbound:c99f54dd494991505fbd7839d47c3b32@send.mailtrap.io:587?encryption=tls
```

Puis testez:
```bash
php bin/console cache:clear
php bin/console app:send-mail
```

Vérifiez l'inbox: https://mailtrap.io/

---

## 🔍 Debug

### Voir les logs
```bash
tail -f var/log/dev.log
```

### Tester email directement
```bash
php bin/console app:send-mail -v
```

### Vider cache
```bash
php bin/console cache:clear
```

### Vérifier BD
```bash
php bin/console doctrine:schema:validate
```

---

## ✅ Checklist Avant Lancement

- [ ] `symfony serve` fonctionne
- [ ] Accès à https://127.0.0.1:8000/demandes
- [ ] Créer demande: OK
- [ ] Email logged: OK
- [ ] Vérification URL accessible: OK
- [ ] Badge affiche: OK
- [ ] (Optionnel) Mailtrap config testé: OK

---

## 🎯 Résumé Flux

```
NEW DEMAND
    ↓
EMAIL SENT (logged to dev.log)
    ↓
USER CLICKS VERIFICATION LINK
    ↓
MARKED AS PRIORITY
    ↓
BADGE ⭐ SHOWS IN LIST & DETAILS
```

**C'est tout! Le système fonctionne! 🎉**

---

**Besoin d'aide?** Voir `EMAIL_CONFIG_GUIDE.md`
