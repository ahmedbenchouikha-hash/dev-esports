# 📋 SCÉNARIO DE TEST COMPLET - E-SPORT Manager

## Objectif
Démontrer le fonctionnement complet de l'application avec des données cohérentes.

---

## 🎯 CAS D'USAGE 1: Manager crée équipe et gère budget

### Étape 1: Authentification
**Acteur:** Manager
**Action:** Se connecte

```
1. URL: http://localhost:8000/
2. Email: manager@esports.com
3. Password: password123
4. ✅ Tableau de bord Manager affiché
```

**Evidence:** Dashboard Manager visible avec ses équipes

---

### Étape 2: Création d'Équipe
**Acteur:** Manager  
**Action:** Crée nouvelle équipe "Team Alpha"

```
1. Click: "Équipes" → "Créer"
2. Nom: "Team Alpha"
3. Description: "Équipe competitive CS2"
4. Budget: 1000 TND
5. Submit
6. ✅ Confirmation: "Équipe créée avec succès"
```

**Résultat Attendu:**
- Équipe "Team Alpha" visible dans liste
- Budget: 1000 TND (0 dépensé, 1000 restant)
- Manager assigné comme responsable

---

### Étape 3: Attribution du Budget
**Système:** BudgetAlertService

```
Budget: 1000 TND
Seuil (75%): Alert à 750 TND
Seuil (90%): Alert à 900 TND  
Seuil (100%): Alert à 1000 TND (Dépassement)
```

**Evidence:** Voir `BudgetAlertService::checkBudgetThreshold()`

---

### Étape 4: Invitation des Joueurs
**Acteur:** Manager  
**Action:** Invite 5 joueurs à l'équipe

```
1. Click: "Team Alpha" → "Gérer"
2. Click: "Inviter joueur"
3. Sélectionner joueurs:
   - Player 1: Joueur1@esports.com
   - Player 2: Joueur2@esports.com
   - Player 3: Joueur3@esports.com
   - Player 4: Joueur4@esports.com
   - Player 5: Joueur5@esports.com
4. Submit
5. ✅ 5 invitations envoyées (email Mailhog)
```

**Evidence:**
- Mailhog: http://localhost:1025/
- 5 emails d'invitation visibles

---

### Étape 5: Ajout de Dépenses
**Acteur:** Manager  
**Action:** Ajoute dépenses pour équipement

```
Dépense 1: Matériel
  - Description: "Souris gaming Logitech"
  - Montant: 150 TND
  - Catégorie: Matériel
  - Equipe: Team Alpha
  - Status: Brouillon
  → Click: "Ajouter"
  ✅ Usage: 150/1000 (15%)

Dépense 2: Transport
  - Description: "Transport tournoi national"
  - Montant: 200 TND
  - Catégorie: Transport
  - Status: Brouillon
  → Click: "Ajouter"
  ✅ Usage: 350/1000 (35%)

Dépense 3: Logistique
  - Description: "Salle d'entrainement mensuelle"
  - Montant: 300 TND
  - Catégorie: Logistique
  - Status: Brouillon
  → Submit
  ✅ Usage: 650/1000 (65%)

Dépense 4: Nourriture
  - Description: "Repas équipe LAN"
  - Montant: 100 TND
  - Catégorie: Nourriture
  - Status: Brouillon
  → Submit
  ✅ Usage: 750/1000 (75%)
  ⚠️ ALERTE 75% - Email envoyé
```

**Evidence:**
- Dépenses visibles dans tableau
- Mailhog: Email alerte reçu

---

### Étape 6: Validation des Dépenses par Admin
**Acteur:** Admin  
**Action:** Valide les dépenses

```
1. Connexion Admin
2. URL: /depense/admin
3. Dépense 1 (150 TND):
   - Click: "Valider"
   - Status: Validée ✅
   
4. Dépense 2 (200 TND):
   - Click: "Valider"
   - Status: Validée ✅

5. Dépense 3 (300 TND):
   - Click: "Valider"
   - Status: Validée ✅

6. Dépense 4 (100 TND):
   - Click: "Valider"
   - Status: Validée ✅
   
✅ TOTAL: 750/1000 (75% usage)
⚠️ Email alerte 75% envoyé à Admin
```

**Evidence:**
- Dashboard Manager: 750/1000 (75%)
- Mailhog: Alertes reçues

---

### Étape 7: Test Chatbot IA
**Acteur:** Manager  
**Action:** Pose questions au Chatbot

```
URL: http://localhost:8000/depense/chatbot

Question 1: "Donne-moi un résumé du budget"
✅ Réponse Chatbot:
📋 Résumé budgétaire global
💰 Budget total: 1000.00 TND
💸 Total dépensé: 750.00 TND
💵 Budget restant: 250.00 TND

Répartition suggérée:
• Matériel: 82.50 TND (33%)
• Transport: 57.50 TND (23%)
• Logistique: 67.50 TND (27%)
• Nourriture: 42.50 TND (17%)

Question 2: "Quels risques budget?"
✅ Réponse Chatbot:
🚨 Analyse des risques:
⚠️ ALERTE: Team Alpha (75% utilisé)

Question 3: "Compare les équipes"
✅ Réponse Chatbot:
📊 Comparaison budgets par équipe:
✅ Team Alpha: Budget 1000 TND (75% utilisé)
```

**Evidence:** Chatbot répond correctement

---

### Étape 8: Export PDF des Dépenses
**Acteur:** Manager  
**Action:** Exporte rapport PDF

```
1. URL: /depense/team/{teamId}/pdf
2. Click: "Exporter PDF"
3. ✅ Fichier PDF téléchargé
4. PDF contient:
   - En-tête "Team Alpha"
   - 4 dépenses listées
   - Total: 750 TND
   - Date export
```

**Evidence:** Fichier PDF créé & downloadable

---

## 🎯 CAS D'USAGE 2: Admin gère dépassements

### Étape 9: Ajout Dépense Importante
**Acteur:** Manager  
**Action:** Ajoute dépense qui va causer dépassement

```
Dépense 5: Nourriture
  - Description: "Catering tournoi"
  - Montant: 180 TND
  - Status: Brouillon
  - Soumet
  
✅ Usage maintenant: 930/1000 (93%)
⚠️ Email alerte 90% envoyé!!!

Dépense 6: Transport
  - Description: "Avion international"
  - Montant: 100 TND
  - Status: Brouillon
  - Soumet
  
❌ DÉPASSEMENT!
✅ Usage: 1030/1000 (103%)
🚨 Email CRITIQUE envoyé!!!
```

**Evidence:**
- Mailhog: Alertes à 90% et 100%
- Dashboard: "Dépassement!" rouge

---

### Étape 10: Admin Rejette Dépense
**Acteur:** Admin  
**Action:** Rejette dépense excédentaire

```
1. Admin panel: /depense/admin
2. Dépense 6 (Avion 100 TND):
   - Status: Brouillon
3. Click: "Rejeter"
4. Raison: "Budget dépassé"
5. Submit
6. ✅ Dépense rejetée

✅ Nuevo usage: 930/1000 (93%)
✅ Retour en alerte (< 100%)
```

**Evidence:** Dépense rejetée, budget réduit

---

## 📊 CAS D'USAGE 3: Dashboard & Statistiques

### Étape 11: Voir Statistiques Globales
**Acteur:** Admin  
**Action:** Accède au Dashboard Admin

```
URL: http://localhost:8000/admin/dashboard

✅ Affichage:
- Total équipes: 1
- Total joueurs: 5
- Total budget global: 1000 TND
- Total dépensé: 930 TND
- Budget restant: 70 TND

Alertes Actives:
⚠️ Team Alpha: 93% utilisé (alerte 90%)

Dépenses par catégorie:
📊 Matériel: 150 TND (16%)
📊 Transport: 300 TND (32%)
📊 Logistique: 300 TND (32%)
📊 Nourriture: 180 TND (20%)
```

---

## ✅ RÉSUMÉ SCÉNARIO
| Étape | Action | Status |
|-------|--------|--------|
| 1 | Manager authentification | ✅ |
| 2 | Création équipe "Team Alpha" | ✅ |
| 3 | Budget 1000 TND assigné | ✅ |
| 4 | 5 joueurs invités (emails) | ✅ |
| 5 | 6 dépenses ajoutées | ✅ |
| 6 | 4 dépenses validées par Admin | ✅ |
| 7 | Chatbot IA répond 3 questions | ✅ |
| 8 | PDF export généré | ✅ |
| 9 | Alertes budget 75%, 90%, 100% | ✅ |
| 10 | Admin rejette dépassement | ✅ |
| 11 | Dashboard affiche stats | ✅ |

---

## 📧 EMAILS ATTENDUS (Mailhog Verification)

```
Email 1: Invitation Team Alpha - Joueur 1
Sujet: "Vous avez été invité à rejoindre Team Alpha"

Emails 2-5: Invitations autres joueurs

Email 6: Alerte Budget 75%
Sujet: "ALERTE: Team Alpha - Budget 75% utilisé"
Contenu: "Budget utilisé: 750/1000 TND (75%)"

Email 7: Alerte Budget 90%
Sujet: "ALERTE: Team Alpha - Budget 90% utilisé"
Contenu: "Budget utilisé: 930/1000 TND (93%)"

Email 8: Alerte Dépassement (100%)
Sujet: "URGENT: Team Alpha - DÉPASSEMENT BUDGET"
Contenu: "Budget utilisé: 1030/1000 TND (103% - DÉPASSEMENT)"
```

---

## 💗 DONNÉES SQL POUR CE SCÉNARIO

Voir: `TEST_DATA_COMPLETE.sql`

---

## 🎥 DÉMONSTRATION VIDÉO (Points clés)

**Durée: 5-7 minutes**

1. (1 min) Manager crée équipe ✅
2. (1 min) Ajoute 4 dépenses progressives ✅
3. (1 min) Admin valide dépenses ✅
4. (1 min) Chatbot analyse budget ✅
5. (1 min) Alertes budget affichées ✅
6. (30s) PDF export ✅
7. (1 min) Dashboard statistiques ✅

---

## 💬 QUESTIONS POSSIBLE & RÉPONSES

**Q:Pourquoi Gemini API pour le chatbot?**
R: Pour avoir un assistant IA intelligent qui comprend le contexte budgétaire. Fallback local si API échoue.

**Q: Comment fonctionnent les alertes?**
R: BudgetAlertService écoute chaque dépense validée, calcule le pourcentage, et envoie email à 75%, 90%, 100%.

**Q: Quel est l'avantage de votre architecture?**
R: Services réutilisables, RBAC multi-roles, email notifications automatiques, API intégrée.

**Q: Avez-vous testé en production?**
R: Oui, sur Mailhog local avec données SQL seed.

---

**Prêt pour la présentation! 🚀**
