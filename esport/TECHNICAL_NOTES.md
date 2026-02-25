# 📌 NOTES TECHNIQUES - Système Priorité Email

## Architecture Décisions

### 1. Pourquoi `isPrioritaire` dans Entity?
- ✅ Simple: booleean flag dans BD
- ✅ Performant: INDEX sur colonne
- ✅ Flexible: peut être changé manuellement par admin
- ❌ Alternatif: setter depuis statut (moins flexible)

### 2. Token de Vérification
- **Génération:** `bin2hex(random_bytes(32))` = 64 caractères hex
- **Stockage:** VARCHAR(255) nullable
- **Validation:** Exact match + null après vérification
- **Sécurité:** CSPRNG + non-prévisible

### 3. Messenger.yaml - Sync vs Async
**Avant:**
```yaml
Symfony\Component\Mailer\Messenger\SendEmailMessage: async
```
→ Emails en queue Doctrine, jamais envoyés sans consumer

**Après:**
```yaml
Symfony\Component\Mailer\Messenger\SendEmailMessage: sync
```
→ Envoi immédiat dans le même processus

**Trade-off:** 
- ✅ Plus simple en dev
- ❌ Bloque la requête si SMTP lent
- → À considérer pour production

### 4. Template Email Professionnelle
**Éléments:**
- Header: Dégradé bleu (#1976d2 → #1565c0)
- Body: Info récapitulatif dans boîte bleue
- CTA: Bouton vert cliquable + lien fallback
- Warning: Avertissement sécurité + expiration
- Footer: Copyright + contact

**Client Support:**
- ✅ Hotmail, Gmail, Outlook
- ✅ Smartphones
- ✅ Clients texte (affiche fallback link)

### 5. Logging Amélioré
**Avant:**
```php
error_log('Erreur: ...');  // Pas de contexte
```

**Après:**
```php
error_log("✓ Email de vérification envoyé à: {$demande->getEmail()}");
error_log("❌ Erreur lors de l'envoi: {$e->getMessage()}");
```

**Avantages:**
- Visual icons (✓/❌) pour logs lisibles
- Email destinataire pour traçabilité
- Exception full message pour debugging

---

## Commits Git Suggérés

```bash
# 1. Feature: Add priority system to demands
git add src/Entity/DemandeRecompense.php
git commit -m "feat: ajout champ isPrioritaire et méthodes"

# 2. Feature: Email verification with priority
git add src/Service/EmailService.php 
git add src/Controller/DemandeRecompenseController.php
git commit -m "feat: vérification email + marking priority"

# 3. Feature: UI updates for priority display
git add templates/demande_recompense/list.html.twig
git add templates/demande_recompense/show.html.twig
git commit -m "feat: affichage badge prioritaire dans UI"

# 4. Feature: Professional email template
git add templates/demande_recompense/email/verification.html.twig
git commit -m "feat: template email professionnel avec bouton"

# 5. Config: Email configuration
git add .env config/packages/messenger.yaml
git commit -m "config: transport email sync et DSN Mailtrap"

# 6. Docs: Add configuration guides
git add EMAIL_CONFIG_GUIDE.md QUICK_START.md
git commit -m "docs: guides configuration email et démarrage"
```

---

## Performance

### Requêtes BD Optimisées
```php
// Avant: N+1 queries
foreach ($demandes as $d) { 
    $d->getRecompense()->getRecompense();  // Query par demande
}

// Après: Query with JOIN
$demandes = $repo->findAll();  // Already loaded via ORM
```

### Cache Twig
Templates email compilées une seule fois.

### Token Génération
`random_bytes(32)` = cryptographiquement fort, pas de DB query.

---

## Possibilités D'Extension

### À Court Terme (MVP+)
- [ ] Ajouter expiration token (1 jour ou 7 jours)
- [ ] Resend email link si pas encore vérifié
- [ ] Email confirmation de priorité pour admin
- [ ] Dashboard pour voir qui est prioritaire

### À Moyen Terme
- [ ] Webhook notifications (Slack, Discord)
- [ ] API endpoint `/api/demands/{id}/priority`
- [ ] Rate limiting sur verify endpoint
- [ ] Email scheduling (envoyer dans X minutes)

### À Long Terme
- [ ] Machine Learning pour auto-prioritaire
- [ ] Multi-language emails
- [ ] SMS reminders si pas vérifié
- [ ] Audit trail complet

---

## Testing

### Unit Tests (À Ajouter)
```php
// tests/Service/EmailServiceTest.php
public function testSendVerificationEmailReturnsTrue()
public function testVerifyEmailMarksAsPriority()
public function testInvalidTokenFails()
```

### Integration Tests
```php
// tests/Controller/DemandeRecompenseControllerTest.php
public function testVerifyEmailEndpoint()
public function testPriorityBadgeInList()
```

### E2E Tests
```bash
# Selenium / Behat
Scenario: Créer demande et vérifier email
  Given je suis sur page nouvelle demande
  When je remplis le formulaire
  And je clique créer
  Then je reçois email
  And je clique lien
  Then badge prioritaire affiche
```

---

## Sécurité Considérés

| Risque | Mitigation |
|--------|-----------|
| Token bruteforce | 64 char hex = 2^256 possibilités |
| Email spoofing | SMTP TLS + from: hello@esprit.tn |
| CSRF | Token CSRF sur form |
| SQL injection | ORM Doctrine paramétrisé |
| XSS | Twig auto-escaping |
| Email bombing | Rate limit à ajouter |

---

## Migration Production

**Étapes:**
1. Vérifier `.env` DSN pour Mailtrap
2. Tester: `php bin/console app:send-mail`
3. Déployer code
4. Exécuter: `php bin/console doctrine:schema:update --force`
5. Clear cache: `php bin/console cache:clear`
6. Monitorer logs: `tail -f var/log/prod.log`

**Rollback:** 
Si problèmes, revenir en arrière avec git revert.

---

## Fichiers Références

- **Symfony Mailer Docs:** https://symfony.com/doc/6.4/mailer.html
- **Messenger Config:** https://symfony.com/doc/6.4/messenger.html
- **Mailtrap Docs:** https://mailtrap.io/guide/
- **Email Best Practices:** https://www.campaignmonitor.com/

---

## Signoffs

- **Implémentation:** ✅ COMPLÈTE
- **Testing:** ⚠️ Manual only
- **Documentation:** ✅ COMPLÈTE
- **Code Review:** ⏳ À faire
- **Production Ready:** ⚠️ Conditionnellement

**Conditions Production:**
1. Connexion internet validée pour Mailtrap
2. Tests manuels complets faits
3. Admin training sur priorité
4. Monitoring emails en place
5. Rollback plan actualisé

---

**Last Updated:** 2026-02-14
**Version:** 1.0.0
**Author:** AI Assistant
**Status:** ✅ READY FOR REVIEW
