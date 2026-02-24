# Manager Role System - Complete Reference Guide

## 📋 Quick Links & Routes

### Player Manager Routes

| Feature | Route | Method | URL |
|---------|-------|--------|-----|
| Submit Request | `player_manager_request` | GET/POST | `/player/manager/request` |
| Check Status | `player_manager_status` | GET | `/player/manager/status` |

### Admin Manager Routes

| Feature | Route | Method | URL |
|---------|-------|--------|-----|
| List Requests | `admin_manager_requests_list` | GET | `/admin/manager-requests` |
| Review Request | `admin_manager_review` | GET/POST | `/admin/manager-requests/{id}/review` |
| Revoke Role | `admin_manager_revoke` | POST | `/admin/manager-requests/{id}/revoke` |

### Protected Routes (Updated)

| Feature | Route | Method | URL | Required Role |
|---------|-------|--------|-----|---------------|
| Create Team | `team_new` | GET/POST | `/teams/new` | ROLE_MANAGER |
| Edit Team | `team_edit` | GET/POST | `/teams/{id}/edit` | ROLE_MANAGER |
| Create Budget | `budget_new` | GET/POST | `/budget/new` | ROLE_MANAGER |
| Edit Budget | `budget_edit` | GET/POST | `/budget/{id}/edit` | ROLE_MANAGER |

---

## 📁 File Organization

### Entity Layer
```
src/Entity/ManagerRequest.php
├─ Properties (11):
│  ├─ id: int
│  ├─ player: Player (FK)
│  ├─ teamName: ?string
│  ├─ motivation: ?string
│  ├─ status: string ('pending'|'approved'|'rejected')
│  ├─ createdAt: DateTime
│  ├─ reviewedAt: ?DateTime
│  ├─ reviewedBy: ?User (FK)
│  └─ adminComment: ?string
├─ Methods: All getters/setters
└─ Lifecycle Callbacks: @PrePersist on createdAt
```

### Controller Layer
```
src/Controller/
├─ PlayerManagerController.php
│  ├─ request(): Handle form submission
│  └─ status(): Display status page
├─ AdminManagerRequestController.php
│  ├─ list(): Show pending/reviewed requests
│  ├─ review(): Process approval/rejection
│  └─ revoke(): Remove manager role
├─ TeamController.php (MODIFIED)
│  ├─ new(): +@IsGranted("ROLE_MANAGER")
│  └─ edit(): +@IsGranted("ROLE_MANAGER")
└─ BudgetController.php (MODIFIED)
   ├─ new(): +@IsGranted("ROLE_MANAGER")
   └─ edit(): +@IsGranted("ROLE_MANAGER")
```

### Form Layer
```
src/Form/
├─ ManagerRequestType.php
│  ├─ teamName: TextType
│  └─ motivation: TextareaType
└─ ManagerRequestReviewType.php
   ├─ status: ChoiceType (approve/reject)
   └─ adminComment: TextareaType
```

### Template Layer
```
templates/
├─ player_manager/
│  ├─ request.html.twig
│  │  └─ Form for submitting request
│  └─ status.html.twig
│     └─ View all user's requests
├─ admin_manager/
│  ├─ list.html.twig
│  │  ├─ Pending requests section
│  │  └─ Reviewed requests section
│  └─ review.html.twig
│     ├─ Player details display
│     └─ Approval form
└─ player/
   └─ dashboard.html.twig (MODIFIED)
      └─ Added manager section
```

### Database Layer
```
migrations/
└─ Version20260221140000.php
   └─ CREATE TABLE manager_request (...)

src/Repository/
└─ ManagerRequestRepository.php
   ├─ findPendingRequests()
   ├─ findByPlayer(Player)
   └─ findPendingByPlayer(Player)
```

---

## 🔄 Data Flow

### Complete Request Lifecycle

```
CREATION:
Player fills form
  ↓
POST /player/manager/request
  ↓
PlayerManagerController::request()
  ↓
Create ManagerRequest entity
  ↓
$em->persist() & flush()
  ↓
Database: manager_request table (status='pending')
  ↓
Redirect to status page

---

REVIEW:
Admin navigates to /admin/manager-requests
  ↓
See pending requests in list
  ↓
Click "Review"
  ↓
GET /admin/manager-requests/{id}/review
  ↓
AdminManagerRequestController::review()
  ↓
Display request with form
  ↓
Admin selects Approve/Reject
  ↓
POST /admin/manager-requests/{id}/review
  ↓
Update manager_request:
├─ status = form data
├─ reviewed_by_id = auth user
├─ reviewed_at = now
├─ admin_comment = comment
└─ If approved: Add ROLE_MANAGER to player
  ↓
$em->flush()
  ↓
Redirect with flash message
  ↓
Player can now create teams/budgets

---

REVOCATION:
Admin on reviewed request
  ↓
Click "Revoke" button
  ↓
POST /admin/manager-requests/{id}/revoke
  ↓
Verify CSRF token
  ↓
Remove ROLE_MANAGER from player roles
  ↓
$em->flush()
  ↓
Player loses team/budget creation access
```

---

## 🗄️ Database Schema

### manager_request Table

```sql
CREATE TABLE manager_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    player_id INT NOT NULL,
    FOREIGN KEY (player_id) REFERENCES player(id) ON DELETE CASCADE,
    INDEX idx_player (player_id),
    
    reviewed_by_id INT NULL,
    FOREIGN KEY (reviewed_by_id) REFERENCES user(id) ON DELETE SET NULL,
    
    team_name VARCHAR(255) NULL,
    motivation LONGTEXT NULL,
    
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    INDEX idx_status (status),
    
    created_at DATETIME NOT NULL,
    reviewed_at DATETIME NULL,
    INDEX idx_created_at (created_at),
    
    admin_comment LONGTEXT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE InnoDB;
```

### Relationships

```
manager_request.player_id → player.id (CASCADE DELETE)
manager_request.reviewed_by_id → user.id (SET NULL)
```

---

## 🔐 Security Architecture

### Authorization Levels

```
UNAUTHENTICATED
  └─ Denied access to all manager routes

ROLE_USER (Regular Player)
  ├─ ✅ Access: /player/dashboard, /player/manager/*
  ├─ ❌ Access: /teams/new, /budget/new
  └─ ❌ Access: /admin/manager-*

ROLE_MANAGER (Approved Player)
  ├─ ✅ Access: All ROLE_USER routes
  ├─ ✅ Access: /teams/new, /teams/{id}/edit
  ├─ ✅ Access: /budget/new, /budget/{id}/edit
  └─ ❌ Access: /admin/manager-*

ROLE_ADMIN (Administrator)
  ├─ ✅ Access: All routes
  └─ ✅ Access: /admin/manager-* (review & revoke)
```

### CSRF Protection

```
All POST forms include:
<input type="hidden" name="_token" value="{{ csrf_token('token_id') }}">

Controller verification:
$this->isCsrfTokenValid('token_id', $request->request->get('_token'))
```

---

## 📊 Business Logic Rules

### Request Submission
```
Rules:
1. Player must have ROLE_USER
2. Cannot have pending request already
3. Cannot already have ROLE_MANAGER
4. Team name is optional
5. Motivation is optional but recommended
6. Auto-set created_at timestamp
7. Status defaults to 'pending'
```

### Admin Review
```
Rules:
1. Must be ROLE_ADMIN
2. Request must be 'pending'
3. Select Approve or Reject
4. Comment is optional
5. Set reviewed_by_id to admin
6. Set reviewed_at to current time
7. If approved: Add ROLE_MANAGER to player
8. No role change if rejected
```

### Role Revocation
```
Rules:
1. Must be ROLE_ADMIN
2. Request must be 'approved'
3. Must have valid CSRF token
4. Remove ROLE_MANAGER from player
5. Keep request in database (audit trail)
```

---

## 🧪 Testing Points

### Unit Tests
- Entity creation and getters/setters
- Repository query methods
- Form validation rules
- Authorization checks

### Integration Tests
- Complete request workflow
- Admin approval process
- Role assignment verification
- Database interaction

### End-to-End Tests
- Player submits request
- Admin reviews and approves
- Player gains team creation ability
- Player can create actual team

---

## 📈 Performance Optimization

### Database Indexes
```
idx_status     → Fast filtering by status
idx_player     → Fast finding player's requests
idx_created_at → Fast sorting by date
Primary Key    → Fast single-record lookup
```

### Query Optimization
```
Good:  findBy(['status' => 'pending'], ['created_at' => 'DESC'])
       → Uses index on status

Good:  find($id)
       → Uses primary key

Avoid: findAll() with filtering in PHP
       → Full table scan
       → Better: Use repository method
```

### Caching
```
Symfony automatically caches:
- Route collection
- Security configuration  
- Form type metadata
- Service container

Clear with: php bin/console cache:clear
```

---

## 🐛 Debugging Guide

### Common Issues & Solutions

```
Issue: "Access Denied" on team creation
Fix:   1. Check user has ROLE_MANAGER
       2. Clear cache: php bin/console cache:clear
       3. Verify roles in DB: SELECT roles FROM player WHERE id=2

Issue: Form not showing
Fix:   1. Verify route name matches path()
       2. Check template file exists
       3. Run: php bin/console debug:router | grep manager

Issue: Admin cannot see requests
Fix:   1. Verify user is ROLE_ADMIN
       2. Check table has data: SELECT COUNT(*) FROM manager_request
       3. Clear cache and refresh

Issue: Approved player can't create teams
Fix:   1. Verify ROLE_MANAGER added: SELECT roles FROM player WHERE id=2
       2. Check TeamController has @IsGranted
       3. Restart PHP if using cache
```

---

## 📚 Code Examples

### Submitting a Request (PHP)
```php
$request = new ManagerRequest();
$request->setPlayer($player);
$request->setTeamName('Phoenix Dynasty');
$request->setMotivation('Build winning team');

$entityManager->persist($request);
$entityManager->flush();
```

### Approving a Request (PHP)
```php
$managerRequest->setStatus('approved');
$managerRequest->setReviewedBy($adminUser);
$managerRequest->setReviewedAt(new DateTime());

$player = $managerRequest->getPlayer();
$roles = $player->getRoles();
$roles[] = 'ROLE_MANAGER';
$player->setRoles(array_unique($roles));

$entityManager->flush();
```

### Checking Access (Twig)
```twig
{% if is_granted('ROLE_MANAGER') %}
    <p>You can create teams!</p>
    <a href="{{ path('team_new') }}">Create Team</a>
{% else %}
    <p>Apply for manager role first.</p>
    <a href="{{ path('player_manager_request') }}">Apply</a>
{% endif %}
```

---

## 📞 Support Contacts

### Documentation Files
- `MANAGER_ROLE_SYSTEM.md` - Technical reference
- `MANAGER_ROLE_QUICK_START.md` - Quick guide
- `MANAGER_ROLE_TESTING.md` - Testing guide
- `MANAGER_ROLE_MODIFIED_FILES.md` - What changed

### Code Comments
Look for comments in:
- `src/Controller/PlayerManagerController.php`
- `src/Controller/AdminManagerRequestController.php`
- `src/Entity/ManagerRequest.php`

---

## ✅ Deployment Verification

```bash
# 1. Migration Status
php bin/console doctrine:migrations:status

# 2. Table Exists
mysql -u user -p database -e "DESC manager_request;"

# 3. Routes Registered
php bin/console debug:router | grep manager

# 4. Cache Cleared
php bin/console cache:clear

# 5. No Errors
php bin/console doctrine:mapping:info
```

---

## 🎯 Next Steps

After deployment:

1. **Test Complete Flow**
   - Player: Submit request
   - Admin: Approve request
   - Player: Create team

2. **User Communication**
   - Announce manager role feature
   - Guide players on how to apply
   - Set expectations on review time

3. **Monitoring**
   - Track request submission rates
   - Monitor approval rates
   - Note any issues

4. **Maintenance**
   - Regular database backups
   - Monitor for errors
   - Update documentation as needed

---

**Version**: 1.0
**Date**: February 21, 2026
**Status**: Production Ready

---

*Complete Reference Guide Finished*
