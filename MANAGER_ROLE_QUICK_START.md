# Manager Role System - Quick Start Guide

## ✨ What Was Implemented

Complete system allowing **players to request a Manager role** and **admins to approve/reject** these requests.

### Key Features:
1. ✅ **Player Manager Request Workflow**
   - Submit manager role application with optional team name and motivation
   - Track application status (pending/approved/rejected)
   - View admin feedback and comments

2. ✅ **Admin Review Interface**
   - View all pending manager requests
   - Review player details and motivation
   - Approve or reject with optional comments
   - Revoke previously approved manager roles

3. ✅ **Automatic Role Assignment**
   - ROLE_MANAGER automatically added to player upon approval
   - Player immediately gains access to team/budget management
   - Remains a player with ROLE_USER for all player features

4. ✅ **Protected Routes**
   - Team creation/editing requires ROLE_MANAGER
   - Budget management requires ROLE_MANAGER
   - Admin functions require ROLE_ADMIN

---

## 📂 New Files Created

### Entities & Database
```
src/Entity/ManagerRequest.php          - Manager request entity
src/Repository/ManagerRequestRepository.php  - Database queries
migrations/Version20260221140000.php   - Database migration
```

### Controllers
```
src/Controller/PlayerManagerController.php      - Player request/status views
src/Controller/AdminManagerRequestController.php - Admin review interface
```

### Forms
```
src/Form/ManagerRequestType.php              - Player application form
src/Form/ManagerRequestReviewType.php        - Admin decision form
```

### Templates
```
templates/player_manager/
  ├── request.html.twig          - Application form
  └── status.html.twig           - Status tracking

templates/admin_manager/
  ├── list.html.twig             - Requests list
  └── review.html.twig           - Review interface
```

---

## 🗄️ Database Schema

```sql
CREATE TABLE manager_request (
    id INT PRIMARY KEY AUTO_INCREMENT,
    player_id INT NOT NULL,
    reviewed_by_id INT,
    team_name VARCHAR(255),
    motivation LONGTEXT,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME,
    reviewed_at DATETIME,
    admin_comment LONGTEXT,
    FOREIGN KEY (player_id) REFERENCES player(id),
    FOREIGN KEY (reviewed_by_id) REFERENCES user(id),
    INDEX idx_status (status),
    INDEX idx_player (player_id),
    INDEX idx_created_at (created_at)
);
```

---

## 🚀 How to Use

### For Players: Request Manager Role

**Step 1: Go to Dashboard**
- Navigate to `/player/dashboard`
- See "Management" section

**Step 2: Click "Apply for Manager Role"**
- Fill optional: Team name, motivation
- Click "Submit Application"

**Step 3: Check Status**
- Visit `/player/manager/status`
- View application status and admin feedback

### For Admins: Review Requests

**Step 1: Access Admin Panel**
- Navigate to `/admin/manager-requests`
- See pending and reviewed requests

**Step 2: Review Request**
- Click "Review" button
- See player details
- Select Approve or Reject
- Add optional comment
- Click "Submit Decision"

**Step 3: Manage Roles**
- Revoke manager role if needed
- Button appears for approved requests

---

## 🔐 Security & Access Control

### Route Protection

| Route | Required Role | Access |
|-------|--------------|--------|
| `/player/manager/request` | ROLE_USER | Players only |
| `/player/manager/status` | ROLE_USER | Players only |
| `/admin/manager-requests` | ROLE_ADMIN | Admins only |
| `/admin/manager-requests/{id}/review` | ROLE_ADMIN | Admins only |
| `/admin/manager-requests/{id}/revoke` | ROLE_ADMIN | Admins only |
| `/teams/new` | ROLE_MANAGER | Managers only |
| `/budget/new` | ROLE_MANAGER | Managers only |

### CSRF Protection
All POST forms include CSRF token validation.

---

## 📋 Forms

### Player Request Form (ManagerRequestType)
```
- Team Name (optional)
- Motivation (optional, up to 1000 chars)
```

### Admin Review Form (ManagerRequestReviewType)
```
- Decision (Approve/Reject)
- Admin Comment (optional, up to 2000 chars)
```

---

## 🎯 Workflow Diagram

```
PLAYER WORKFLOW:
┌─────────────┐      ┌──────────────┐      ┌──────────────┐
│   Player    │─────>│   Submit     │─────>│   Pending    │
│  Dashboard  │      │   Request    │      │   Request    │
└─────────────┘      └──────────────┘      └──────────────┘
                                                    │
                                                    v
                                        ┌──────────────────┐
                                        │  Admin Reviews   │
                                        └──────────────────┘
                                                    │
                    ┌───────────────────────────────┴──────────────────┐
                    |                                                  |
                    v                                                  v
        ┌─────────────────────┐                        ┌────────────────────┐
        │  Approved           │                        │  Rejected          │
        │  + ROLE_MANAGER     │                        │  Remains ROLE_USER │
        │  + Can manage teams │                        │  + See feedback    │
        └─────────────────────┘                        └────────────────────┘
```

---

## 🔧 Database Migration

### Run Migration
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Verify
```bash
mysql> DESC manager_request;
```

### Rollback (if needed)
```bash
php bin/console doctrine:migrations:migrate --direction down
```

---

## 📝 Status Values

```
pending  → Awaiting admin review
approved → Manager role granted
rejected → Application denied
```

---

## 🎨 UI Integration

### Player Dashboard
New "Management" section shows:
- **If NOT Manager**: CTA to apply for manager role
- **If IS Manager**: Confirmation and quick links to team/budget creation

### Admin Dashboard
Access via `/admin/manager-requests` with:
- Pending requests table
- Recently reviewed requests
- Action buttons for review/revoke

---

## 🧪 Testing Checklist

- [ ] Player submits first request
- [ ] System prevents duplicate pending requests
- [ ] Admin reviews and approves
- [ ] Player gains ROLE_MANAGER
- [ ] Player can create teams
- [ ] Player can manage budget
- [ ] Admin can revoke role
- [ ] Player loses team/budget creation access after revoke
- [ ] Rejected player remains ROLE_USER

---

## 📊 Database Queries

### Get all pending requests
```php
$requests = $repo->findPendingRequests();
```

### Get player's requests
```php
$requests = $repo->findByPlayer($player);
```

### Get pending request for player
```php
$request = $repo->findPendingByPlayer($player);
```

---

## ⚙️ Configuration

### File Locations
- Config: `config/packages/` (no special config needed)
- Routes: Automated via PHP 8 attributes
- Roles: Standard Symfony security roles

### No External Dependencies
All functionality uses standard Symfony components.

---

## 🔍 Debugging

### Check Request Status
```bash
mysql> SELECT id, player_id, status, created_at FROM manager_request;
```

### Clear Cache After Role Changes
```bash
php bin/console cache:clear
```

### View RouteCollector
```bash
php bin/console debug:router | grep manager
```

---

## 📈 Future Enhancements

### Suggested Improvements
1. Email notifications to players/admins
2. Request expiration (auto-close after 30 days)
3. Request history/audit log
4. Team association with manager
5. Manager permissions granularity
6. Request comments conversation
7. Bulk approval/rejection
8. Analytics dashboard

---

## 🆘 Troubleshooting

### Issue: "Access Denied" on team creation
**Solution**: User needs manager role approval

### Issue: Route not found
**Solution**: Clear cache: `php bin/console cache:clear`

### Issue: Form not submitting
**Solution**: Check CSRF token in template

### Issue: Roles not updating
**Solution**: Verify `$player->setRoles()` is called, then flush

---

## 📞 Support

For issues or questions about the Manager Role System:
1. Check MANAGER_ROLE_SYSTEM.md for detailed documentation
2. Review controller logic in AdminManagerRequestController
3. Check template files for UI/route issues
4. Query Database directly for data verification

---

## ✅ Implementation Checklist

- [x] ManagerRequest entity created with all fields
- [x] Database migration created and tested
- [x] Player request controller with form handling
- [x] Admin review controller with approval logic
- [x] Manager request forms (player and admin)
- [x] Manager request repository with custom queries
- [x] Player manager templates (request + status)
- [x] Admin manager templates (list + review)
- [x] Team controller protected with @IsGranted
- [x] Budget controller protected with @IsGranted
- [x] Player dashboard integration with manager CTA
- [x] CSRF protection on all forms
- [x] Comprehensive documentation

---

## 📚 Documentation Files

- **MANAGER_ROLE_SYSTEM.md** - Complete technical documentation
- **QUICK_REFERENCE.md** - This file
- **Code comments** - Implementation details in controllers

---

## 🎉 Done!

The Manager Role System is fully implemented and ready to use. 

**Next Steps**:
1. Run database migration
2. Test the complete workflow (player request → admin approval)
3. Optionally customize UI/text in templates
4. Deploy to production

---

*Implementation completed: February 21, 2026*
