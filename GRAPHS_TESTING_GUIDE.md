# 📊 Test & Démonstration des Graphiques

## 🧪 Tests en Local

### Prérequis:
```bash
✓ Serveur Symfony lancé
✓ MySQL connecté
✓ Données test chargées
```

---

## 🌐 Endpoints à Tester

### 1. **Ouvrir le Dashboard**
```
URL: http://localhost:8000/budget/dashboard
Ou: http://localhost:8000/team/dashboard

Attendu:
✅ 4 graphiques ApexCharts chargés
✅ KPI cards avec valeurs correctes
✅ Données interpolées depuis DB
```

### 2. **API Test - Résumé Global**
```bash
curl http://localhost:8000/api/charts/summary | jq '.'

Réponse attendue:
{
  "total_budget": 1000.00,
  "total_spent": 750.00,
  "total_remaining": 250.00,
  "utilization_percent": 75.00,
  "team_count": 1,
  "expense_count": 6,
  "expense_validated": 4
}
```

### 3. **API Test - Catégories**
```bash
curl http://localhost:8000/api/charts/depenses-by-category | jq '.'

Réponse attendue:
{
  "labels": ["materiel", "transport", "logistique", "nourriture"],
  "data": [150.00, 200.00, 300.00, 100.00],
  "total": 750.00,
  "count": 4,
  "percentages": [20.0, 26.67, 40.0, 13.33]
}
```

### 4. **API Test - Équipes**
```bash
curl http://localhost:8000/api/charts/teams-budget-comparison | jq '.'

Réponse attendue:
{
  "teams": ["Team Alpha"],
  "allocated": [1000.00],
  "used": [750.00],
  "remaining": [250.00]
}
```

### 5. **API Test - Évolution**
```bash
curl http://localhost:8000/api/charts/budget-evolution | jq '.'

Réponse attendue:
{
  "dates": ["2026-02-25"],
  "cumulative": [750.00],
  "remaining": [250.00],
  "budget_allocated": 1000.00,
  "total_spent": 750.00
}
```

---

## 🎨 Vérifications Visuelles

### Dashboard Test Checklist:

```
Chart 1 - Budget par Équipe (Horizontal Bar):
  □ Affiche "Team Alpha"
  □ Barre bleue = 1000 TND (alloué)
  □ Barre rouge = 750 TND (utilisé)
  □ Libellés montants visibles
  □ Legend en bas avec couleurs

Chart 2 - Dépenses par Catégorie (Donut):
  □ 4 couleurs distinctes
  □ Secteur logistique plus grand (40%)
  □ Pourcentages affichés sur labels
  □ Total au centre = 750.00 TND
  □ Legend interactive

Chart 3 - Évolution Budget (Area + Line):
  □ Courbe area rouge (dépensé cumul)
  □ Courbe line bleue (restant)
  □ Axe X: dates
  □ Axe Y gauche: dépensé
  □ Axe Y droit: restant
  □ Zoom + pan fonctionnels

Chart 4 - Statistiques Dépenses (Grouped Bar):
  □ 4 catégories sur abscisse
  □ Barres bleu = nombre total
  □ Barres verte = validées
  □ Montants en tooltip
  □ Legend en bas
```

---

## 📱 Test Responsive

### Desktop (1920px):
```javascript
// Dans console
window.innerWidth  // 1920
// Graphiques: 2 colonnes (Charts 1&2), full width (Charts 3&4)
```

### Tablet (768px):
```javascript
// Devtools: Toggle device toolbar → iPad Air
// Graphiques: Stack vertical, légèrement plus petits
```

### Mobile (375px):
```javascript
// Devtools: Toggle device toolbar → iPhone 12
// Graphiques: Stack vertical, heights 300px, compact
```

---

## 🖱️ Interactivité Test

### Chart 1 - Hover:
```
Au survol barre:
  ✓ Tooltip affiche "Team Alpha: 1000 TND"
  ✓ Barre s'illumine
  ✓ Shadow effect
```

### Chart 2 - Click Legend:
```
Click sur legend "materiel":
  ✓ Secteur materiel disparaît
  ✓ Autres secteurs se redessinent
  ✓ Pourcentages recalculés
  ✓ Re-click pour afficher
```

### Chart 3 - Zoom:
```
Drag sur graphique:
  ✓ Zone sélectionnée zoomée
  ✓ Axe X montre zoom
  ✓ Reset button apparaît
  ✓ Pan possible après zoom
```

### Chart 4 - Hover:
```
Au survol barre:
  ✓ Tooltip: "materiel: 2 dépenses"
  ✓ Barre highlight
```

---

## 🔧 Debugging Console

### Si graphique ne s'affiche pas:

```javascript
// Step 1: Vérifier ApexCharts chargé
console.log(typeof ApexCharts);  // Doit afficher "function"

// Step 2: Vérifier données disponibles
console.log(depenses);  // Array avec dépenses

// Step 3: Vérifier container element
document.getElementById('chartDepensesByCategory');  // Doit exister

// Step 4: Check error dans console
// Vérifier network tab (pas de 404 apexcharts)

// Step 5: Test API directement
fetch('/api/charts/summary').then(r => r.json()).then(console.log);
```

---

## 📊 Données de Test (Test Data)

### Depuis TEST_DATA_COMPLETE.sql:
```sql
Team: Team Alpha
Budget: 1000 TND

Expenses (validées):
1. Souris gaming         150 TND (materiel)
2. Transport tournoi     200 TND (transport)
3. Salle d'entrainement  300 TND (logistique)
4. Repas équipe          100 TND (nourriture)
   ────────────────
   TOTAL                 750 TND (75%)

Expenses (autres):
5. Catering (brouillon)  180 TND
6. Avion (rejetée)       100 TND
```

### Expected Visualizations:

```
Chart 1 (Categories):
  materiel:   150 TND (20%)    [Red]
  transport:  200 TND (26.67%) [Teal]
  logistique: 300 TND (40%)    [Blue]
  nourriture: 100 TND (13.33%) [Orange]
  
Chart 2 (Teams):
  Team Alpha: [Allocated: 1000] [Used: 750]
  
Chart 3 (Evolution):
  Single date: cumulative = 750, remaining = 250
  
Chart 4 (Stats):
  materiel:   2 total, 1 validée
  transport:  1 total, 1 validée
  logistique: 1 total, 1 validée
  nourriture: 1 total, 1 validée
```

---

## 🚀 Scenario Complet de Test

### Étape 1: Setup (5 min)
```bash
php bin/console cache:clear
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
mysql -u root esportdevvvvvv < TEST_DATA_COMPLETE.sql
```

### Étape 2: Démarrer serveur (2 min)
```bash
symfony server:start
# Ou
php -S localhost:8000 -t public
```

### Étape 3: Accéder Dashboard (1 min)
```
http://localhost:8000/budget/dashboard
Login: manager@esports.com / password123
```

### Étape 4: Visual Inspection (5 min)
```
□ Tous 4 graphiques visibles
□ KPI cards avec bonnes valeurs
□ Aucune erreur console
□ Responsive au resize
```

### Étape 5: API Testing (5 min)
```bash
curl http://localhost:8000/api/charts/summary | jq
# Devrait afficher:
# - total_budget: 1000.00
# - total_spent: 750.00
# - utilization_percent: 75.00
```

### Étape 6: Interactivity Testing (5 min)
```
□ Hover chart 1 → tooltip
□ Click legend chart 2 → show/hide
□ Drag chart 3 → zoom
□ Hover chart 4 → highlight
```

**Total: ~25 minutes**

---

## 📸 Screenshots pour Présentation

### À capturer:

1. **Dashboard Complet:**
   ```
   Titre: "Dashboard Financier E-Sport Manager"
   Affiche: KPI cards + 4 graphiques
   Dimensions: 1920x1080 (fullscreen)
   ```

2. **Chart 1 Zoom:**
   ```
   Titre: "Comparaison Budget par Équipe"
   Focus: Barres colorées Team Alpha
   Dimensions: 800x400
   ```

3. **Chart 2 Zoom:**
   ```
   Titre: "Répartition Dépenses par Catégorie"
   Focus: Donut avec 4 secteurs
   Dimensions: 800x400
   ```

4. **Chart 3 Zoom:**
   ```
   Titre: "Évolution du Budget"
   Focus: Courbes rouge + bleue
   Dimensions: 1000x400
   ```

5. **Mobile Dashboard:**
   ```
   Device: iPhone 12 (375x812)
   Affiche: Graphiques stacked verticalement
   Dimensions: Full device
   ```

---

## 🎯 Success Criteria

### ✅ Minimum Viable:
```
[x] Tous 4 graphiques affichent
[x] Données correctes depuis DB
[x] Pas d'erreur console
[x] API endpoints fonctionnels
```

### ✅ Good:
```
[x] Mobile responsive OK
[x] Interactions zoom/pan/toggle
[x] Tooltips détaillés
[x] Couleurs cohérentes
```

### ✅ Excellent:
```
[x] Performance: < 100ms load
[x] 100% test coverage
[x] Documentation complète
[x] Screenshots pour présentation
```

---

## 📝 Rapport de Test Template

```markdown
## Test Report - Charts Implementation
**Date:** [Date]
**Tester:** [Votre nom]
**Status:** ✅ PASS / ❌ FAIL

### Environment:
- Browser: [Chrome 123]
- Device: [Desktop 1920x1080]
- Data: [TEST_DATA_COMPLETE.sql]

### Results:
- [x] Dashboard loads
- [x] All 4 charts render
- [x] Data points correct
- [x] API endpoints work
- [x] Mobile responsive
- [x] Interactivity works

### Issues Found:
- None

### Performance:
- Chart load time: < 500ms
- API response time: < 100ms
- No memory leaks

### Recommendations:
- 🚀 Ready for production

**Sign-off:** ✅ Approved
```

---

## 🔄 Checklist Final Avant Présentation

- [ ] Dashboard chargé et testé
- [ ] Tous graphiques visibles
- [ ] API endpoints testés (via curl)
- [ ] Mobile responsive vérifié
- [ ] Pas d'erreurs console
- [ ] Screenshots capturés (5 images)
- [ ] Data correctes (750/1000 TND)
- [ ] Interactivité zoom/pan fonctionnelle
- [ ] Documentation doc commit: GRAPHS_IMPLEMENTATION_SUMMARY.md
- [ ] Git commit avec message clair

---

**Timestamp:** 25 Février 2026 16:45 CET
**Status:** 🟢 READY FOR DEMO

