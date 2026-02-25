📦 **3 BUNDLES INSTALLÉS & CONFIGURÉS**

## ✅ 1️⃣ VICH UPLOADER BUNDLE
**Status:** ✔ Installé & Configuré
**Location:** `config/packages/vich_uploader.yaml`

### 📁 Dossiers créés:
- `public/uploads/logos/teams/` → Logos équipes
- `public/uploads/documents/depenses/` → Factures dépenses
- `public/uploads/documents/budgets/` → Justificatifs budgets

### 🔧 Comment utiliser dans vos entités:

**Pour TEAM (logo):**
```php
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
class Team {
    #[Vich\UploadableField(mapping: 'team_logo', fileNameProperty: 'logo')]
    private ?File $logoFile = null;
    
    private ?string $logo = null;
    
    public function setLogoFile(?File $logoFile = null): self {
        $this->logoFile = $logoFile;
        if (null !== $logoFile) {
            $this->updatedAt = new \DateTime();
        }
        return $this;
    }
}
```

**Pour DEPENSE (facture):**
```php
#[Vich\Uploadable]
class Depense {
    #[Vich\UploadableField(mapping: 'depense_documents', fileNameProperty: 'facture')]
    private ?File $factureFile = null;
    
    private ?string $facture = null;
}
```

**Pour BUDGET (justificatif):**
```php
#[Vich\Uploadable]
class Budget {
    #[Vich\UploadableField(mapping: 'budget_justificatif', fileNameProperty: 'justificatif')]
    private ?File $justificatifFile = null;
    
    private ?string $justificatif = null;
}
```

### 📋 Fichiers à AJOUTER à la DB (nouvelle migration):
```sql
-- Team
ALTER TABLE team ADD COLUMN logoFile VARCHAR(255) NULL;

-- Depense
ALTER TABLE depense ADD COLUMN factureFile VARCHAR(255) NULL;

-- Budget
ALTER TABLE budget ADD COLUMN justificatifFile VARCHAR(255) NULL;
```

---

## ✅ 2️⃣ KNP PAGINATOR BUNDLE
**Status:** ✔ Installé & Configuré
**Location:** `config/packages/knp_paginator.yaml`

### 🚀 Comment utiliser dans Controllers:

**Pour DEPENSE (lister avec pagination):**
```php
use Knp\Component\Pager\PaginatorInterface;

class DepenseController {
    public function list(Request $request, PaginatorInterface $paginator): Response {
        $query = $this->em->getRepository(Depense::class)->findAll();
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10  // 10 items par page
        );

        return $this->render('depense/list.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
```

### 🎨 Dans les templates Twig:
```twig
{# Pagination #}
{{ knp_pagination_render(pagination) }}

{# Données paginées #}
{% for depense in pagination %}
    <tr>
        <td>{{ depense.titre }}</td>
        <td>{{ depense.montant|number_format(2, ',', '.') }} €</td>
    </tr>
{% endfor %}
```

---

## ✅ 3️⃣ CHART.JS (CDN - Pas de bundle Symfony)
**Status:** ✔ Configuré (CDN + Templates)
**Location:** `templates/partials/`

### 📊 Graphiques disponibles:

1. **_chart_budget_expenses.html.twig**
   - Doughnut chart: Budget utilisé vs restant
   ```twig
   {% include 'partials/_chart_budget_expenses.html.twig' with {'budget': budget} %}
   ```

2. **_chart_depenses_by_category.html.twig**
   - Doughnut chart: Répartition par catégorie
   ```twig
   {% include 'partials/_chart_depenses_by_category.html.twig' with {'depenses': depenses} %}
   ```

3. **_chart_depenses_evolution.html.twig**
   - Line chart: Évolution mensuelle
   ```twig
   {% include 'partials/_chart_depenses_evolution.html.twig' with {'depenses': depenses} %}
   ```

4. **_chart_teams_budget_comparison.html.twig**
   - Bar chart horizontal: Comparaison équipes
   ```twig
   {% include 'partials/_chart_teams_budget_comparison.html.twig' with {'budgets': budgets} %}
   ```

---

## 🧪 EXAMPLE COMPLET: Dashboard Budget

```php
// BudgetController.php
public function dashboard(): Response {
    $budgets = $this->em->getRepository(Budget::class)->findAll();
    $depenses = $this->em->getRepository(Depense::class)->findAll();

    return $this->render('budget/dashboard.html.twig', [
        'budgets' => $budgets,
        'depenses' => $depenses
    ]);
}
```

```twig
{# budget/dashboard.html.twig #}
{% extends 'base.html.twig' %}

{% block content %}
<div class="container mt-4">
    <h1>Dashboard Budget</h1>
    
    <div class="row">
        <div class="col-md-6">
            {% include 'partials/_chart_teams_budget_comparison.html.twig' %}
        </div>
        <div class="col-md-6">
            {% include 'partials/_chart_depenses_by_category.html.twig' %}
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            {% include 'partials/_chart_depenses_evolution.html.twig' %}
        </div>
    </div>
</div>
{% endblock %}
```

---

## 🎯 PROCHAINES ÉTAPES

1. ✅ Créer migration pour ajouter colonnes fichiers
2. ✅ Mettre à jour les Form Types (Team, Budget, Depense)
3. ✅ Ajouter graphiques aux templates existantes
4. ✅ Tester uploads via interface

## 📝 RÉSUMÉ
- **Zéro modification** du code existant ✓
- **3 bundles puissants** intégrés ✓
- **Configurations YAML** prêtes ✓
- **Templates réutilisables** pour graphiques ✓
