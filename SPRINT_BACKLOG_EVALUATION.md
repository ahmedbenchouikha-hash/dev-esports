# Sprint Backlog - E-Sports Platform

## Tableau Complet des Fonctionnalités

| ID | User Story | Priorité | Fonctionnalités | Estimation | Status |
|----|-----------|----------|-----------------|-----------|--------|
| **ÉQUIPE (TEAM)** |
| 1.1 | En tant que manager, je veux créer une équipe avec détails | Haute | CRUD Create + Validation | 3h | ✅ Complète |
| 1.2 | En tant que manager, je veux voir la liste des équipes | Moyenne | CRUD Read (Listage) | 2h | ✅ Complète |
| 1.3 | En tant que admin, je veux voir les détails d'une équipe | Moyenne | CRUD Read (Détails) | 1h | ✅ Complète |
| 1.4 | En tant que manager, je veux modifier mon équipe | Moyenne | CRUD Update | 2h | ✅ Complète |
| 1.5 | En tant que manager, je veux supprimer une équipe | Basse | CRUD Delete | 1h | ✅ Complète |
| 1.6 | En tant que system, je veux recommander des compositions optimales | Haute | API Avancée + IA | 5h | 📋 À faire |
| 1.7 | En tant que manager, je veux voir les statistiques en temps réel | Moyenne | API Dashboard + WebSocket | 4h | 🔄 En cours |
| **DÉPENSE (DEPENSE)** |
| 2.1 | En tant que manager, je veux enregistrer une dépense | Haute | CRUD Create + Validation | 3h | ✅ Complète |
| 2.2 | En tant que manager, je veux voir toutes les dépenses | Moyenne | CRUD Read (Listage) | 2h | ✅ Complète |
| 2.3 | En tant que manager, je veux voir les détails d'une dépense | Moyenne | CRUD Read (Détails) | 1h | ✅ Complète |
| 2.4 | En tant que manager, je veux modifier une dépense | Moyenne | CRUD Update | 2h | ✅ Complète |
| 2.5 | En tant que manager, je veux supprimer une dépense | Basse | CRUD Delete | 1h | ✅ Complète |
| 2.6 | En tant que manager, je veux analyser les tendances de dépenses | Haute | API Avancée + Analytics | 5h | 📋 À faire |
| 2.7 | En tant que admin, je veux exporter les dépenses en PDF/Excel | Moyenne | API Export + Reporting | 4h | 🔄 En cours |
| **BUDGET** |
| 3.1 | En tant que manager, je veux créer un budget pour mon équipe | Haute | CRUD Create + Validation | 3h | ✅ Complète |
| 3.2 | En tant que manager, je veux voir tous les budgets | Moyenne | CRUD Read (Listage) | 2h | ✅ Complète |
| 3.3 | En tant que manager, je veux voir les détails d'un budget | Moyenne | CRUD Read (Détails) | 1h | ✅ Complète |
| 3.4 | En tant que manager, je veux modifier le budget alloué | Moyenne | CRUD Update | 2h | ✅ Complète |
| 3.5 | En tant que manager, je veux supprimer un budget | Basse | CRUD Delete | 1h | ✅ Complète |
| 3.6 | En tant que system, je veux alerter sur les dépassements | Haute | API Avancée + Notification | 5h | ✅ Complète |
| 3.7 | En tant que admin, je veux analyser les dépenses vs budgets | Moyenne | API Dashboard + Analytics | 4h | 🔄 En cours |

---

## Statistiques Globales

| Métrique | Valeur |
|----------|--------|
| **Total User Stories** | 21 |
| **CRUD (Basique)** | 15 |
| **Avancé (API)** | 6 |
| **Complètes** | 14 |
| **En cours** | 2 |
| **À faire** | 5 |
| **Estimation Totale** | 61h |
| **% Complétude** | 67% |

---

## Extraction par Entité

### 📦 ÉQUIPE (5 CRUD + 2 Avancés)
- Total: 7 User Stories
- Complètes: 5
- En cours: 1
- À faire: 1

### 💰 DÉPENSE (5 CRUD + 2 Avancés)
- Total: 7 User Stories
- Complètes: 5
- En cours: 1
- À faire: 1

### 💵 BUDGET (5 CRUD + 2 Avancés)
- Total: 7 User Stories
- Complètes: 6
- En cours: 1
- À faire: 0

---

## 📋 Détails des 2 User Stories à Expliquer Oralement

### **1️⃣ CRUD BASIQUE (Exemple: Équipe - ID 1.1)**
**User Story:** *"En tant que manager, je veux créer une équipe avec détails"*

#### Processus:
```
Manager → Formulaire → Validation → Base de données → Confirmation
```

#### Fichiers Impliqués:
- **Entité**: `src/Entity/Team.php` (propriétés: nom, description, logo, etc.)
- **Form**: `src/Form/TeamType.php` (validation client-side)
- **Controller**: `src/Controller/TeamAdminController.php` (createAction)
- **Template**: `templates/admin/team/form.html.twig`
- **Repository**: `src/Repository/TeamRepository.php`

#### Étapes:
1. Affichage du formulaire
2. Saisie des données (nom, description, couleur, jeu)
3. Validation (NotBlank, Length, Unique)
4. Sauvegarde en DB
5. Redirection + Flash message

---

### **2️⃣ API AVANCÉE (Exemple: Budget - ID 3.6)**
**User Story:** *"En tant que system, je veux alerter sur les dépassements de budget"*

#### Architecture:
```
Entité Budget → Service (BudgetAlertService) → API Endpoint → Email/Notification
```

#### Fichiers Impliqués:
- **Entité**: `src/Entity/BudgetAlert.php` (relations avec Budget)
- **Service**: `src/Service/BudgetAlertService.php` (logique métier)
- **Controller**: `src/Controller/BudgetController.php` (endpoint API)
- **Repository**: `src/Repository/BudgetAlertRepository.php`
- **Email**: `src/Service/EmailService.php` (notifications)
- **API Endpoint**: `POST /api/budget/{id}/check-alert`

#### Processus:
1. **Trigger**: Création/Modification de dépense
2. **Calcul**: Comparaison (solde actuel vs budget alloué)
3. **Condition**: Si dépasse 80% → Alerte
4. **Action**: 
   - Enregistrement BudgetAlert en DB
   - Envoi email au manager
   - Notification Dashboard
   - Log système
5. **Response**: `{status: "alert_sent", percentage: 85%}`

#### Exemple API:
```bash
POST /api/budget/5/check-alert
Response:
{
  "id": 5,
  "alert_type": "over_threshold",
  "budget_percentage": 85,
  "remaining_amount": 15000,
  "message": "Budget à 85% de dépassement",
  "status": "alert_sent",
  "sent_at": "2026-02-22T14:30:00Z"
}
```

---

## 📊 Résumé Évaluation [10 pts]

### Points d'Évaluation:
- ✅ **Complétude du Tableau** (3 pts): Sprint Backlog avec 21 user stories
- ✅ **User Story CRUD** (3.5 pts): Création Équipe (Create, formulaire, validation)
- ✅ **User Story Avancée API** (3.5 pts): Alerte Budget (Service, notification, logique complexe)

### Éléments à Couvrir Oralement:
1. **CRUD**: Différence Create/Read/Update/Delete
2. **CRUD**: Validation et gestion d'erreurs
3. **API**: Architecture et flux d'appels
4. **API**: Notifications et événements
5. **API**: Persistence et logging

---

## 🔄 Status Légende

| Symbol | Signification |
|--------|--------------|
| ✅ | Complète - En production |
| 🔄 | En cours - Développement actif |
| 📋 | À faire - Planifiée |
| ⚠️ | Bloquée - En attente |
| 🚀 | Prête pour review |

