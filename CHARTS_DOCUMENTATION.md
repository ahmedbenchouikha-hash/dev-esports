# 📊 Système de Graphiques Statistiques - ApexCharts

## 🎯 Vue d'Ensemble
Le système de graphiques a été modernisé avec **ApexCharts** pour fournir des visualisations interactives et responsive du budget et des dépenses.

---

## 📈 Graphiques Disponibles

### 1. **Dépenses par Catégorie** (Donut Chart)
**Fichier:** `templates/partials/_chart_depenses_by_category.html.twig`

**Affiche:**
- Répartition des dépenses validées par catégorie
- Pourcentage de chaque catégorie
- Total dépensé au centre
- Couleurs distinctes pour chaque catégorie

**Données requises:**
```javascript
depenses: [
  {
    id: 1,
    montant: 150.00,
    categorie: 'materiel',
    statut: 'validée'
  },
  // ...
]
```

**Logique:**
- Filtre les dépenses par statut === 'validée'
- Regroupe par catégorie
- Calcule les pourcentages
- Affiche montant TND par catégorie

---

### 2. **Budget par Équipe** (Horizontal Bar Chart)
**Fichier:** `templates/partials/_chart_teams_budget_comparison.html.twig`

**Affiche:**
- Comparaison Budget Alloué vs Budget Utilisé
- Une barre par équipe
- Valeurs en TND avec libellés

**Données requises:**
```javascript
budgets: [
  {
    id: 1,
    team: { name: 'Team Alpha' },
    montant_alloue: 1000.00,
    montant_utilise: 750.00
  },
  // ...
]
```

**Logique:**
- Même scale pour comparer équipes
- Horizontal view pour lire noms équipes
- Tooltip au survol

---

### 3. **Évolution du Budget** (Area + Line Chart)
**Fichier:** `templates/partials/_chart_budget_evolution.html.twig`

**Affiche:**
- Courbe de dépenses cumulées
- Courbe du budget restant
- Évolution chronologique
- Zoom et pan interactifs

**Données requises:**
```javascript
depenses: [
  {
    montant: 150.00,
    date_creation: '2026-02-25',
    statut: 'validée'
  },
  // ...
]
budget: {
  montant_alloue: 1000.00
}
```

**Logique:**
- Trie par date sortie
- Calcule cumul = somme montants
- Calcule restant = alloc - cumul
- Axe Y gauche: dépensé (rouge)
- Axe Y droit: restant (turquoise)

---

### 4. **Statistiques Dépenses** (Grouped Bar Chart)
**Fichier:** `templates/partials/_chart_expense_stats.html.twig`

**Affiche:**
- Nombre total dépenses par catégorie
- Nombre dépenses validées par catégorie
- Comparaison visuelle

**Données requises:**
```javascript
depenses: [
  {
    categorie: 'materiel',
    montant: 150.00,
    statut: 'validée'
  },
  // ...
]
```

**Logique:**
- Compte dépenses par catégorie
- Sous-compte par statut
- Barre verte = validées
- Barre bleue = total

---

## 🔧 Installation & Configuration

### 1. **ApexCharts CDN** (Automatique)
```html
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
```
Chargé dans chaque fichier graphique.

### 2. **Responsive - Mobile Ready**
Tous les graphiques utilisent `responsive: true` et s'adaptent:
- Desktop: Taille optimale
- Tablet: Réduit légèrement
- Mobile: Stack vertical

### 3. **Interactivité**
- **Hover:** Affiche données précises
- **Click:** Legend interactive (show/hide series)
- **Zoom:** Suite à `chart.zoom` enabled
- **Pan:** Drag pour naviguer dates

---

## 📊 Passer les Données aux Templates

### À partir du Contrôleur:

```php
// BudgetController.php
public function dashboard(BudgetRepository $budgetRepo, DepenseRepository $depenseRepo): Response
{
    $budgets = $budgetRepo->findAll();
    $depenses = $depenseRepo->findAll();
    
    return $this->render('budget/dashboard_complete.html.twig', [
        'budgets' => $budgets,        // Pour charts 1 & 2
        'depenses' => $depenses,      // Pour charts 1, 3 & 4
    ]);
}
```

### Transformation Doctine à JSON:

```twig
{# Dans le template #}
{% include 'partials/_chart_budget_evolution.html.twig' with {
    'depenses': depenses,
    'budget': budgets[0]|default({})
} %}
```

Les entités Doctrine sont **automatiquement converties en JSON** via `|json_encode|raw`

---

## 🎨 Couleurs & Thème

### Palette Utilisée:
```
Rouge (Dépensé):     #FF6B6B
Turquoise (Restant): #4ECDC4
Bleu (Alloué):       #45B7D1
Orange (Alerte):     #FFA07A
Vert (Validé):       #51CF66
```

### Personnaliser:
```javascript
colors: ['#FF6B6B', '#4ECDC4', '#45B7D1'],  // Array de couleurs
```

---

## 🚀 Cas d'Utilisation

### Scénario: Manager crée équipe & budget
1. ✅ Crée Team Alpha
2. ✅ Alloue 1000 TND
3. ✅ Ajoute 4 dépenses (750 TND)
4. **Résultat Dashboard:**
   - Chart 1: Donut montrant 4 catégories
   - Chart 2: Bar montrant 750/1000 utilisé
   - Chart 3: Line montrant évolution dans le temps
   - Chart 4: Grouped bars montrant 4 dépenses total

### Scénario: Alerte Budget 75%+
- Chart 3 affiche courbe de budget restant ≤ 250 TND
- Color changes (red zone)
- Manager peut ajuster budget rapidement

---

## 🔍 Debugging

### Si graphique n'apparaît pas:

1. **Vérifier console navigateur:**
   ```javascript
   console.log('depenses:', depenses);  // Doit afficher array
   ```

2. **Vérifier données JSON:**
   ```
   Si depenses est null/undefined → JSON convert failed
   ```

3. **Vérifier ApexCharts chargé:**
   ```javascript
   console.log(typeof ApexCharts);  // Doit afficher "function"
   ```

4. **Vérifier ID container:**
   ```html
   <!-- Element doit exister -->
   <div id="chartDepensesByCategory"></div>
   ```

---

## 📱 Responsive Breakpoints

```javascript
responsive: [{
    breakpoint: 480,  // Mobile
    options: {
        chart: { height: 300 },
        legend: { position: 'bottom' }
    }
}]
```

---

## 🛠️ Améliorations Futures

1. **Filtrage par date:** Ajouter date picker
2. **Export PDF:** Exporter graphiques
3. **Comparaison équipes:** Multi-select
4. **Prévisions:** Projection budget fin période
5. **Real-time:** WebSocket updates
6. **Drill-down:** Clic catégorie → détails dépenses

---

## 📝 Checklist Integration

- [x] ApexCharts CDN ajouté
- [x] 4 graphiques créés
- [x] Dashboard template mis à jour
- [x] Données Twig passées correctement
- [x] Mobile responsive
- [x] Couleurs cohérentes
- [ ] Tests E2E
- [ ] Documentation complète (✓ Done)

