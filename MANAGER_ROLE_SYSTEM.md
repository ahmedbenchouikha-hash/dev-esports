# Manager Role System - Implementation Guide

## Overview

This implementation enables players to request the **Manager role**, which grants them the ability to:
- Create and manage their own esports teams
- Manage team budgets and expenses
- Retain all player functionality (join teams, participate in tournaments, etc.)

## System Architecture

### 1. Database Schema

#### New Table: `manager_request`
```sql
CREATE TABLE manager_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    reviewed_by_id INT NULL,
    team_name VARCHAR(255) NULL,
    motivation LONGTEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    reviewed_at DATETIME NULL,
    admin_comment LONGTEXT NULL,
    FOREIGN KEY (player_id) REFERENCES player(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by_id) REFERENCES user(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_player (player_id),
    INDEX idx_created_at (created_at)
)
```

### 2. Entity Classes

#### ManagerRequest Entity
**Location**: `src/Entity/ManagerRequest.php`

**Key Properties**:
- `id`: Unique identifier
- `player`: Reference to the Player entity
- `teamName`: Optional desired team name
- `motivation`: Optional motivation text
- `status`: pending | approved | rejected
- `createdAt`: Request creation timestamp
- `reviewedAt`: Review completion timestamp
- `reviewedBy`: Admin who reviewed the request
- `adminComment`: Admin's feedback

**Methods**:
- All getters/setters
- `setCreatedAtValue()`: Auto-populates creation date

### 3. Roles

#### ROLE_MANAGER
New role added to player accounts when a manager request is approved.

**Players with ROLE_MANAGER can**:
- Access `/teams/new` - Create new teams
- Access `/teams/{id}/edit` - Edit their teams
- Access `/budget/new` - Create team budgets
- Access `/budget/{id}/edit` - Manage team budgets
- Keep ROLE_USER - Retain all player functionality

---

## User Workflows

### Workflow 1: Player Requests Manager Role

**Step 1**: Player visits their dashboard
- Navigate to `/player/dashboard`
- See "Become a Manager" section

**Step 2**: Click "Apply for Manager Role"
- Route: `player_manager_request` (GET/POST)
- URL: `/player/manager/request`

**Step 3**: Fill Application Form
```
Fields:
- Team Name (optional): Desired team name
- Motivation (optional): Why they want to be a manager
```

**Step 4**: Submit Request
- Request stored in `manager_request` table
- Status: `pending`
- Player redirected to status page

**Step 5**: Check Application Status
- Route: `player_manager_status`
- URL: `/player/manager/status`
- View all submitted requests and their status

---

### Workflow 2: Admin Reviews Manager Requests

**Step 1**: Admin views pending requests
- Route: `admin_manager_requests_list`
- URL: `/admin/manager-requests`
- Lists all pending and recently reviewed requests

**Step 2**: Admin clicks "Review" on a pending request
- Route: `admin_manager_review`
- URL: `/admin/manager-requests/{id}/review`

**Step 3**: Admin Reviews Application
**Displays**:
- Player information
- Current team (if any)
- Requested team name (if provided)
- Motivation text
- Request submission date

**Step 4**: Admin Makes Decision
```
Form Fields:
- Decision (Approve/Reject)
- Admin Comment: Feedback for the player
```

**Step 5**: Admin Submits Decision
- Request status updated: approved | rejected
- `reviewedBy`: Set to current admin
- `reviewedAt`: Set to current timestamp
- `adminComment`: Admin's feedback saved

**If Approved**:
- ROLE_MANAGER added to player's roles
- Player can immediately access manager features
- Flash message: "Manager request approved!"

**If Rejected**:
- Player remains ROLE_USER
- Admin comment explains the rejection
- Flash message: "Manager request rejected"

**Step 6**: Revoke Manager Role (Optional)
- Route: `admin_manager_revoke`
- URL: `/admin/manager-requests/{id}/revoke`
- POST method with CSRF token
- Removes ROLE_MANAGER from player
- Only available for approved requests

---

## File Structure

### New Files Created

```
src/
├── Controller/
│   ├── PlayerManagerController.php          # Player-side requests & status
│   └── AdminManagerRequestController.php    # Admin review interface
├── Entity/
│   └── ManagerRequest.php                   # Manager request entity
├── Form/
│   ├── ManagerRequestType.php               # Player request form
│   └── ManagerRequestReviewType.php         # Admin review form
├── Repository/
│   └── ManagerRequestRepository.php         # Database queries

templates/
├── player_manager/
│   ├── request.html.twig                    # Request submission form
│   └── status.html.twig                     # Application status
└── admin_manager/
    ├── list.html.twig                       # Pending requests list
    └── review.html.twig                     # Review request form

migrations/
└── Version20260221140000.php                # Database migration
```

### Modified Files

```
src/Controller/
├── TeamController.php                       # Added @IsGranted("ROLE_MANAGER") to new/edit
└── BudgetController.php                     # Added @IsGranted("ROLE_MANAGER") to new/edit

templates/
└── player/
    └── dashboard.html.twig                  # Added Manager section with CTA
```

---

## Routes

### Player Routes

| Route Name | Method | Path | Handler |
|-----------|--------|------|---------|
| `player_manager_request` | GET/POST | `/player/manager/request` | Request form |
| `player_manager_status` | GET | `/player/manager/status` | View status |

### Admin Routes

| Route Name | Method | Path | Handler |
|-----------|--------|------|---------|
| `admin_manager_requests_list` | GET | `/admin/manager-requests` | List all requests |
| `admin_manager_review` | GET/POST | `/admin/manager-requests/{id}/review` | Review request |
| `admin_manager_revoke` | POST | `/admin/manager-requests/{id}/revoke` | Revoke manager role |

---

## Form Classes

### ManagerRequestType (Player Form)
- `teamName` (TextType): Optional team name
- `motivation` (TextareaType): Optional motivation

**CSS Classes**: `form-control`

### ManagerRequestReviewType (Admin Form)
- `status` (ChoiceType): Approve/Reject dropdown
- `adminComment` (TextareaType): Admin feedback

**No data_class** - Form data processed manually

---

## Repository Methods

### ManagerRequestRepository

```php
// Find all pending requests
findPendingRequests(): ManagerRequest[]

// Find all requests by player
findByPlayer(Player $player): ManagerRequest[]

// Find pending request for specific player
findPendingByPlayer(Player $player): ?ManagerRequest
```

---

## Security

### Access Control

**PlayerManagerController**:
- Requires: `ROLE_USER`
- Methods: All routes

**AdminManagerRequestController**:
- Requires: `ROLE_ADMIN`
- Methods: All routes

**TeamController** (Modified):
- `new()`: Requires `ROLE_MANAGER`
- `edit()`: Requires `ROLE_MANAGER`

**BudgetController** (Modified):
- `new()`: Requires `ROLE_MANAGER`
- `edit()`: Requires `ROLE_MANAGER`

### CSRF Protection

All POST forms include CSRF token validation:
```twig
{{ csrf_token('token_name') }}
```

---

## User Interface

### Player Dashboard Enhancement

**New Section**: "Management"

**If NOT Manager**:
```
┌─────────────────────────────────────┐
│ 👑 Become a Manager                 │
├─────────────────────────────────────┤
│ Ready to lead? Apply for the        │
│ manager role to create and manage   │
│ your own teams...                   │
│                                     │
│ [Apply for Manager Role]            │
│ [Check Application Status]          │
└─────────────────────────────────────┘
```

**If IS Manager**:
```
┌─────────────────────────────────────┐
│ ✅ You are a Manager!               │
├─────────────────────────────────────┤
│ You can now create and manage       │
│ teams and budgets.                  │
│                                     │
│ [Create Team]                       │
│ [View Manager Applications]         │
└─────────────────────────────────────┘
```

### Admin Manager Requests Page

**Pending Section**:
- List of pending requests with review buttons
- Player info, requested team name, date

**Reviewed Section**:
- Recent approved/rejected requests
- Can revoke approved requests

---

## Business Logic

### Request Creation
1. Player submits form
2. Check if already pending request exists
3. Check if already has ROLE_MANAGER
4. Create new ManagerRequest with status='pending'
5. Store in database

### Request Review
1. Admin accesses review page
2. Form pre-loads decision options
3. Admin selects Approve/Reject
4. Admin optionally adds comment
5. Upon submission:
   - Update request status
   - Set reviewed date/admin
   - If approved: Add ROLE_MANAGER to player
   - Flush changes to database

### Role Enforcement
- Redirect non-managers away from team/budget creation
- Show manager-only options in UI based on role

---

## Migration Instructions

### 1. Execute Migration
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Verify Database
```sql
DESC manager_request;
```

Should show:
- `player_id` FOREIGN KEY
- `reviewed_by_id` FOREIGN KEY
- Status indices

### 3. Clear Cache
```bash
php bin/console cache:clear
```

---

## Testing Scenarios

### Scenario 1: Happy Path
1. Player (not manager) submits request
2. Admin approves request
3. Player can now create teams
4. Verify ROLE_MANAGER in player roles

### Scenario 2: Rejection
1. Player submits request
2. Admin rejects with comment
3. Player views rejection reason
4. Player remains ROLE_USER

### Scenario 3: Role Revocation
1. Player is approved manager
2. Admin revokes manager role
3. Player redirected from team creation
4. Verify ROLE_MANAGER removed

### Scenario 4: Duplicate Request Prevention
1. Player submits request
2. Player attempts second request
3. Flash warning about pending request
4. Redirect to status page

---

## Future Enhancements

1. **Email Notifications**
   - Notify player when request is reviewed
   - Notify admins of new requests

2. **Request History**
   - Show player all past requests (not just pending)
   - Display approval/rejection timeline

3. **Auto-Expiration**
   - Expire pending requests after 30 days
   - Allow resubmission

4. **Team Association**
   - Link manager directly to teams they create
   - Restrict editing to team creator

5. **Permissions Management**
   - Admins assign managers to existing teams
   - Manager hierarchies (head manager, assistant)

---

## Troubleshooting

### Issue: "Access Denied" on team creation
**Cause**: User doesn't have ROLE_MANAGER
**Solution**: Submit manager request and wait for approval

### Issue: Pending request not visible to admin
**Cause**: Wrong status in database
**Solution**: Check `manager_request.status = 'pending'`

### Issue: Approved player can't create teams
**Cause**: Cache not cleared after role change
**Solution**: Clear cache: `php bin/console cache:clear`

### Issue: Form not submitting
**Cause**: Missing CSRF token
**Solution**: Ensure `form_start()` wraps form fields

---

## Summary

The Manager Request System provides:
✅ Player-initiated role promotion workflow
✅ Admin review and approval interface
✅ Secure access control with ROLE_MANAGER
✅ Comprehensive audit trail (reviewer, date, comment)
✅ Clean, intuitive user experience
✅ Database-backed persistence
✅ Extensible architecture for future enhancements

All requirements from the original objective have been implemented.
