# 🔧 Sidebar & Player Navigation - Fixed

## Issues Fixed ✅

### 1. **Sidebar Not Visible for Players**
**Problem**: Sidebar only showed for admin routes  
**Fix**: Updated `base.html.twig` to show sidebar for both admin AND player routes
```twig
{% set is_admin = 'admin' in app.request.attributes.get('_route', '') %}
{% set is_player = 'player' in app.request.attributes.get('_route', '') %}

{% if is_admin or is_player %}
    {% include 'partials/sidebar.html.twig' %}
{% endif %}
```

### 2. **Player Menu Missing Depense & Budget Options**
**Problem**: Players didn't have navigation for expenses and budget  
**Fix**: Added complete player menu section with:
- **My Dashboard** - Link to player dashboard
- **Expenses** - View/Create expenses dropdown
- **Budget** - View/Create budget dropdown (managers only)
- **My Teams** - Managers can create/manage teams
- **Apply for Manager** - Non-managers can request manager role

### 3. **Route Error: `/player/manager` Not Found**
**Problem**: Route `/player/manager` doesn't exist; should be `/player/manager/request`  
**Status**: Routes are correct:
- ✅ `/player/manager/request` - Apply for manager role
- ✅ `/player/manager/status` - Check application status
- ✅ Sidebar now links to correct routes

### 4. **Players Can't See Admin Dashboard**
**Problem**: Players shouldn't see admin dashboard  
**Fix**: Player menu only shows player-related options

---

## Player Sidebar Menu Structure

```
PLAYER
├── My Dashboard (player_dashboard)
├── Expenses
│   ├── My Expenses (depense_index)
│   └── New Expense (depense_new)
├── Budget
│   ├── My Budgets (budget_index)
│   └── Create Budget (budget_new) [Managers Only]
├── My Teams (if Manager)
│   ├── All Teams (team_index)
│   └── Create Team (team_new)
└── Apply for Manager (if Not Manager)
    └── Apply (player_manager_request)

PUBLIC
├── View Matches
├── View Tournaments
└── View Teams
```

---

## Files Modified

### 1. `templates/base.html.twig`
- Added player route detection: `{% set is_player = 'player' in ... %}`
- Updated sidebar condition to show for both admin and player: `{% if is_admin or is_player %}`

### 2. `templates/partials/sidebar.html.twig`
- Expanded PLAYER section with full menu structure
- Added Expenses dropdown with view/create options
- Added Budget dropdown with conditional manager options
- Added My Teams section for managers
- Added Apply for Manager option for non-managers

---

## What Players See Now

### When NOT Manager:
- 👤 My Dashboard
- 💰 Expenses (view/create)
- 📊 Budget (view only)
- 👑 **Apply for Manager** ← Apply here!
- 🌐 View Matches, Tournaments, Teams

### When IS Manager (after approval):
- 👤 My Dashboard
- 💰 Expenses (view/create)
- 📊 Budget (view/create)
- 👑 **My Teams** (create/manage)
- 🌐 View Matches, Tournaments, Teams

---

## Testing Checklist ✅

- [ ] Login as player
- [ ] See sidebar with menu items
- [ ] See "Apply for Manager" in sidebar
- [ ] Click "Apply for Manager" → Goes to form
- [ ] Fill form and submit
- [ ] Click "My Dashboard" → Shows manager section with "Pending" status
- [ ] Login as admin
- [ ] Go to `/admin/manager-requests`
- [ ] Review and approve request
- [ ] Login as player again
- [ ] See "My Teams" instead of "Apply for Manager"
- [ ] Can access `/teams/new` and `/budget/new`

---

## Routes Verified

| Route | Name | Purpose |
|-------|------|---------|
| `/player/manager/request` | `player_manager_request` | Apply for manager |
| `/player/manager/status` | `player_manager_status` | Check status |
| `/admin/manager-requests` | `admin_manager_requests_list` | Admin review |
| `/admin/manager-requests/{id}/review` | `admin_manager_review` | Review details |
| `/admin/manager-requests/{id}/revoke` | `admin_manager_revoke` | Revoke role |

---

## Cache Cleared ✅

```bash
php bin/console cache:clear
```

All changes are now active!

---

## Next Steps

1. **Test the workflow**:
   - Login as test player
   - Look for the sidebar with navigation
   - Click "Apply for Manager"
   - Submit the form
   - Check status
   - Login as admin and approve
   - Verify manager access

2. **Check Routes**:
   - Run: `php bin/console debug:router | grep player_manager`
   - Should see both routes listed

3. **Verify Database**:
   - Check that manager_request table exists
   - Check that player roles are updated after approval

---

**Status**: ✅ Ready to test!
