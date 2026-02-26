# 🚀 API REST - Charts Data Endpoints

## Overview
Les endpoints suivants fournissent les données brutes pour les graphiques en JSON.

---

## 📍 Endpoints Disponibles

### 1. **Dépenses par Catégorie**
```
GET /api/charts/depenses-by-category
```

**Réponse:**
```json
{
  "labels": ["materiel", "transport", "logistique"],
  "data": [150.00, 200.00, 300.00],
  "total": 650.00,
  "count": 3,
  "percentages": [23.08, 30.77, 46.15]
}
```

**Utilisation JavaScript:**
```javascript
fetch('/api/charts/depenses-by-category')
  .then(res => res.json())
  .then(data => {
    console.log('Catégories:', data.labels);
    console.log('Montants:', data.data);
    console.log('Total:', data.total);
  });
```

---

### 2. **Comparaison Budget par Équipe**
```
GET /api/charts/teams-budget-comparison
```

**Réponse:**
```json
{
  "teams": ["Team Alpha", "Team Beta"],
  "allocated": [1000.00, 800.00],
  "used": [750.00, 450.00],
  "remaining": [250.00, 350.00]
}
```

**Utilisation JavaScript:**
```javascript
fetch('/api/charts/teams-budget-comparison')
  .then(res => res.json())
  .then(data => {
    data.teams.forEach((team, i) => {
      console.log(`${team}: ${data.used[i]}/${data.allocated[i]}`);
    });
  });
```

---

### 3. **Évolution du Budget**
```
GET /api/charts/budget-evolution/{teamId?}
```

**Paramètres:**
- `teamId` (optional): Filtrer par équipe ID

**Réponse:**
```json
{
  "dates": ["2026-02-20", "2026-02-22", "2026-02-25"],
  "cumulative": [150.00, 350.00, 650.00],
  "remaining": [850.00, 650.00, 350.00],
  "budget_allocated": 1000.00,
  "total_spent": 650.00
}
```

**Utilisation JavaScript:**
```javascript
// Pour équipe spécifique
fetch('/api/charts/budget-evolution/1')
  .then(res => res.json())
  .then(data => {
    console.log('Dates:', data.dates);
    console.log('Dépensé (cumul):', data.cumulative);
    console.log('Restant:', data.remaining);
  });

// Pour tous
fetch('/api/charts/budget-evolution')
  .then(res => res.json())
  .then(data => { /* ... */ });
```

---

### 4. **Statistiques Dépenses**
```
GET /api/charts/expense-stats
```

**Réponse:**
```json
{
  "categories": ["materiel", "transport", "logistique"],
  "stats": [
    {
      "count": 2,
      "validated": 1,
      "pending": 1,
      "rejected": 0,
      "total_amount": 150.00
    },
    {
      "count": 1,
      "validated": 1,
      "pending": 0,
      "rejected": 0,
      "total_amount": 200.00
    },
    {
      "count": 1,
      "validated": 1,
      "pending": 0,
      "rejected": 0,
      "total_amount": 300.00
    }
  ]
}
```

**Utilisation JavaScript:**
```javascript
fetch('/api/charts/expense-stats')
  .then(res => res.json())
  .then(data => {
    data.categories.forEach((cat, i) => {
      const stat = data.stats[i];
      console.log(`${cat}: ${stat.validated} validées, ${stat.total_amount} TND`);
    });
  });
```

---

### 5. **Résumé Global**
```
GET /api/charts/summary
```

**Réponse:**
```json
{
  "total_budget": 2000.00,
  "total_spent": 1200.00,
  "total_remaining": 800.00,
  "utilization_percent": 60.00,
  "team_count": 2,
  "expense_count": 6,
  "expense_validated": 4
}
```

**Utilisation JavaScript:**
```javascript
fetch('/api/charts/summary')
  .then(res => res.json())
  .then(data => {
    console.log(`Utilisation: ${data.utilization_percent}%`);
    console.log(`Dépenses: ${data.total_spent}/${data.total_budget}`);
  });
```

---

## 🔄 Rafraîchissement en Temps Réel

### Auto-refresh toutes les 30 secondes:
```javascript
setInterval(() => {
  fetch('/api/charts/summary')
    .then(res => res.json())
    .then(data => {
      document.getElementById('utilization').textContent = 
        data.utilization_percent + '%';
    });
}, 30000); // 30 secondes
```

### Dashboard temps réel avec WebSocket (futur):
```javascript
const socket = new WebSocket('ws://localhost:8000/ws/charts');
socket.onmessage = (event) => {
  const data = JSON.parse(event.data);
  updateCharts(data);
};
```

---

## 🛡️ Sécurité

### Authentification (futur):
```php
// À ajouter dans ChartApiController
#[IsGranted('ROLE_ADMIN')]
public function summary(...) { /* ... */ }
```

### Rate Limiting:
```yaml
# config/packages/api_platform.yaml
api_platform:
  swagger:
    enable: true
  rate_limiter:
    api:
      enabled: true
      policy: 'sliding_window'
      limit: 100
      interval: '30 minutes'
```

---

## 📊 Cas d'Utilisation Avancés

### 1. **Combiner plusieurs endpoints:**
```javascript
Promise.all([
  fetch('/api/charts/depenses-by-category').then(r => r.json()),
  fetch('/api/charts/summary').then(r => r.json()),
  fetch('/api/charts/teams-budget-comparison').then(r => r.json())
])
.then(([categories, summary, teams]) => {
  console.log('Categories:', categories);
  console.log('Summary:', summary);
  console.log('Teams:', teams);
  // Créer une vue custom combinée
});
```

### 2. **Export CSV:**
```javascript
async function exportToCSV() {
  const data = await fetch('/api/charts/expense-stats').then(r => r.json());
  let csv = 'Catégorie,Nombre,Validées,Montant\n';
  
  data.categories.forEach((cat, i) => {
    const stat = data.stats[i];
    csv += `${cat},${stat.count},${stat.validated},${stat.total_amount}\n`;
  });
  
  // Télécharger le fichier
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'stats.csv';
  a.click();
}
```

### 3. **Alertes Dynamiques:**
```javascript
async function checkBudgetAlert() {
  const data = await fetch('/api/charts/summary').then(r => r.json());
  
  if (data.utilization_percent >= 90) {
    showAlert('DANGER: Budget à 90%+ ⚠️', 'danger');
  } else if (data.utilization_percent >= 75) {
    showAlert('Attention: Budget à 75%+', 'warning');
  }
}

// Vérifier toutes les 5 min
setInterval(checkBudgetAlert, 300000);
```

---

## 🧪 Tests avec cURL

```bash
# Test 1: Résumé
curl http://localhost:8000/api/charts/summary

# Test 2: Dépenses par catégorie
curl http://localhost:8000/api/charts/depenses-by-category

# Test 3: Équipes
curl http://localhost:8000/api/charts/teams-budget-comparison

# Test 4: Évolution équipe 1
curl http://localhost:8000/api/charts/budget-evolution/1

# Test 5: Statistiques
curl http://localhost:8000/api/charts/expense-stats

# Format pretty JSON
curl http://localhost:8000/api/charts/summary | jq '.'
```

---

## 📖 Documentations API

### Swagger UI (si installé):
```
http://localhost:8000/api/doc
```

### Postman Collection:
Créer nouvelle collection avec:
- Base URL: `http://localhost:8000`
- Endpoints: Ajouter chaque GET endpoint listés ci-dessus

---

## 🔮 Évolutions Futures

- [ ] Ajouter filtrage par date: `?from=2026-02-01&to=2026-02-28`
- [ ] Ajouter pagination: `?page=1&limit=10`
- [ ] Ajouter tri: `?sort=amount&order=desc`
- [ ] Ajouter groupement: `?group_by=team`
- [ ] Cache results: `?cache=3600` (1 heure)
- [ ] WebSocket réel temps
- [ ] Comparaison périodes: `?compare=month`

