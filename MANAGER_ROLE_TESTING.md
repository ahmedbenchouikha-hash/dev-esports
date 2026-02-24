# Manager Role System - Testing & Examples

## 📋 Test Scenarios

### Scenario 1: Happy Path (Full Approval Workflow)

**Precondition**: Player user "AlexChen" is logged in

**Steps**:
1. Navigate to `/player/dashboard`
2. Scroll to "Management" section
3. Click "Apply for Manager Role"
4. Fill form:
   - Team Name: "Phoenix Dynasty"
   - Motivation: "I want to build a competitive team"
5. Click "Submit Application"
6. **Expected**: Flash message "Your manager request has been submitted!"
7. Redirected to `/player/manager/status`
8. **Expected**: Request shows status "Pending" with submitted date

**Admin Steps**:
1. Login as admin
2. Navigate to `/admin/manager-requests`
3. **Expected**: See AlexChen's request in "Pending Requests"
4. Click "Review"
5. **Expected**: See player details and motivation
6. Select "Approve"
7. Add comment: "Strong motivation. Approved."
8. Click "Submit Decision"
9. **Expected**: Flash "Manager request approved!"

**Verification**:
1. AlexChen navigates to `/player/dashboard`
2. **Expected**: Management section shows approval message
3. AlexChen clicks "Create Team"
4. **Expected**: Can access team creation form
5. Database check:
   ```sql
   SELECT roles FROM player WHERE id = 2; -- Should contain "ROLE_MANAGER"
   ```

---

### Scenario 2: Rejection Workflow

**Precondition**: Player "Sarah" submits manager request

**Admin Steps**:
1. Review Sarah's request
2. Select "Reject"
3. Comment: "Experience required. Try again after 6 months."
4. Submit

**Verification**:
1. Sarah checks `/player/manager/status`
2. **Expected**: Status shows "Rejected"
3. **Expected**: Can see admin comment
4. **Expected**: Can still apply again (separate request)
5. **Expected**: Doesn't have ROLE_MANAGER

---

### Scenario 3: Duplicate Request Prevention

**Precondition**: Player with pending request

**Steps**:
1. Player tries to submit another request
2. **Expected**: Flash "You already have a pending manager request"
3. **Expected**: Redirected to status page

**Verification**:
```sql
SELECT COUNT(*) FROM manager_request 
WHERE player_id = ? AND status = 'pending';
-- Should be 1, not 2
```

---

### Scenario 4: Manager Can Create Teams

**Precondition**: Approved manager

**Steps**:
1. Navigate to `/teams/new`
2. **Expected**: Form loads (not 403 error)
3. Fill team details
4. Submit
5. **Expected**: Team created successfully

**Non-Manager**: 
1. Navigate to `/teams/new`
2. **Expected**: 403 Access Denied error

---

### Scenario 5: Role Revocation

**Precondition**: Approved manager with ROLE_MANAGER

**Admin Steps**:
1. Go to `/admin/manager-requests`
2. Find approved manager in "Recently Reviewed" section
3. Click "Revoke"
4. **Expected**: Confirmation dialog
5. Confirm
6. **Expected**: Flash "Manager role revoked"

**Verification**:
1. Former manager tries `/teams/new`
2. **Expected**: 403 Access Denied
3. Database:
   ```sql
   SELECT roles FROM player WHERE id = ?;
   -- Should NOT contain "ROLE_MANAGER"
   ```

---

## 🔍 Database Examples

### View All Manager Requests
```sql
SELECT 
    mr.id,
    u.username as player,
    mr.team_name,
    mr.status,
    mr.created_at,
    admin.username as reviewed_by,
    mr.reviewed_at
FROM manager_request mr
LEFT JOIN player p ON mr.player_id = p.id
LEFT JOIN user u ON p.id = u.id
LEFT JOIN user admin ON mr.reviewed_by_id = admin.id
ORDER BY mr.created_at DESC;
```

### Find Pending Requests
```sql
SELECT p.id, u.username, mr.created_at
FROM manager_request mr
JOIN player p ON mr.player_id = p.id
JOIN user u ON p.id = u.id
WHERE mr.status = 'pending'
ORDER BY mr.created_at ASC;
```

### Check Active Managers
```sql
SELECT u.username, u.roles
FROM user u
WHERE u.discr = 'player'
AND u.roles LIKE '%ROLE_MANAGER%'
ORDER BY u.username;
```

### Manager Activity Timeline
```sql
SELECT 
    u.username,
    mr.status,
    mr.created_at as requested,
    mr.reviewed_at as decided,
    admin.username as decided_by,
    mr.admin_comment
FROM manager_request mr
JOIN player p ON mr.player_id = p.id
JOIN user u ON p.id = u.id
LEFT JOIN user admin ON mr.reviewed_by_id = admin.id
WHERE mr.player_id = ?
ORDER BY mr.created_at;
```

---

## 🎯 API Endpoints Reference

### Player Endpoints

**Submit Manager Request**
```
POST /player/manager/request
Form Data:
  - teamName (optional): string
  - motivation (optional): string (max 1000)
  
Response: 
  - Success: Redirect to /player/manager/status with flash
  - Error: Redisplay form with validation errors
```

**Get Request Status**
```
GET /player/manager/status

Response: renders status.html.twig with:
  - requests: Array of ManagerRequest objects
  - isManager: Boolean
```

---

### Admin Endpoints

**List All Requests**
```
GET /admin/manager-requests

Response: renders list.html.twig with:
  - pendingRequests: Array
  - reviewedRequests: Array (last 20)
```

**Review Single Request**
```
GET /admin/manager-requests/{id}/review
- Show form

POST /admin/manager-requests/{id}/review
Form Data:
  - status: 'approved' | 'rejected'
  - adminComment (optional): string
  
Response: 
  - Success: Redirect with flash + add ROLE_MANAGER if approved
  - Error: Redisplay form
```

**Revoke Manager Role**
```
POST /admin/manager-requests/{id}/revoke
Headers:
  - _token: CSRF token

Response:
  - Success: Redirect with flash + remove ROLE_MANAGER
  - Error: Redirect with error message
```

---

## 🧪 Unit Test Examples

### Test Player Can Request Manager Role
```php
public function testPlayerCanSubmitManagerRequest()
{
    $player = $this->findPlayer('AlexChen');
    
    $request = new ManagerRequest();
    $request->setPlayer($player);
    $request->setTeamName('My Team');
    $request->setMotivation('I want to lead');
    
    $this->entityManager->persist($request);
    $this->entityManager->flush();
    
    $this->assertNotNull($request->getId());
    $this->assertEquals('pending', $request->getStatus());
}
```

### Test Admin Can Approve Request
```php
public function testAdminCanApproveRequest()
{
    $request = $this->findPendingRequest();
    $admin = $this->findAdmin();
    
    $request->setStatus('approved');
    $request->setReviewedBy($admin);
    $request->setReviewedAt(new DateTime());
    
    $player = $request->getPlayer();
    $roles = $player->getRoles();
    $roles[] = 'ROLE_MANAGER';
    $player->setRoles(array_unique($roles));
    
    $this->entityManager->flush();
    
    $this->assertContains('ROLE_MANAGER', $player->getRoles());
}
```

### Test Non-Manager Cannot Create Team
```php
public function testNonManagerCannotCreateTeam()
{
    $player = $this->findPlayer('NonManager');
    $this->authenticateAs($player);
    
    $response = $this->client->request('GET', '/teams/new');
    
    $this->assertEquals(403, $response->getStatusCode());
}
```

### Test Manager Can Create Team
```php
public function testManagerCanCreateTeam()
{
    $manager = $this->findManagerPlayer();
    $this->authenticateAs($manager);
    
    $response = $this->client->request('GET', '/teams/new');
    
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('Team Name', $response->getContent());
}
```

---

## 🐛 Common Debugging Checks

### Issue: Player can create teams without manager role

**Checks**:
```bash
# 1. Verify @IsGranted is present
grep -n "IsGranted.*ROLE_MANAGER" src/Controller/TeamController.php

# 2. Check cache
php bin/console cache:clear

# 3. Verify role in DB
SELECT roles FROM player WHERE id = 2;  -- Should see array with ROLE_MANAGER

# 4. Check routing
php bin/console debug:router | grep team_new
```

### Issue: Admin cannot see pending requests

**Checks**:
```bash
# 1. Check database has data
SELECT COUNT(*) FROM manager_request WHERE status = 'pending';

# 2. Verify route exists
php bin/console debug:router | grep manager

# 3. Check admin access
# Login as admin and verify ROLE_ADMIN exists

# 4. Clear cache
php bin/console cache:clear
```

### Issue: Manager request form not showing in templates

**Checks**:
```bash
# 1. Verify template file exists
ls -la templates/player_manager/

# 2. Check route name in Twig matches controller
grep "player_manager_request" templates/player/dashboard.html.twig
grep "player_manager_request" src/Controller/PlayerManagerController.php

# 3. Verify form is building correctly
php bin/console debug:container | grep ManagerRequestType
```

---

## 📊 Performance Notes

### Query Performance

**Optimal queries**:
```php
// Uses index on status
$repo->findBy(['status' => 'pending'], ['createdAt' => 'DESC']);

// Uses index on player_id
$repo->findByPlayer($player);

// Direct by ID (primary key)
$repo->find($id);
```

**Non-optimal queries**:
```php
// No index - full table scan
$repo->findAll(); // Better: findPendingRequests()

// No index on motivation - full text search
// Acceptable for admin use, not for user-facing
```

---

## 🔐 Security Examples

### CSRF Token in Template
```twig
<form method="POST" action="{{ path('admin_manager_revoke', {'id': request.id}) }}">
    <input type="hidden" name="_token" value="{{ csrf_token('revoke_' ~ request.id) }}">
    <button type="submit">Revoke</button>
</form>
```

### Controller Validation
```php
if (!$this->isCsrfTokenValid('revoke_' . $managerRequest->getId(), 
                             $request->request->get('_token'))) {
    $this->addFlash('error', 'Invalid CSRF token.');
    return $this->redirectToRoute('admin_manager_requests_list');
}
```

### Authorization Check
```php
#[Route('/{id}/review', name: 'review')]
#[IsGranted('ROLE_ADMIN')]  // Only admins
public function review(ManagerRequest $managerRequest, ...) { ... }
```

---

## 📈 Monitoring Queries

### Manager Request Statistics
```sql
SELECT 
    status,
    COUNT(*) as count,
    AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_review_hours
FROM manager_request
GROUP BY status;
```

### Approval Rate
```sql
SELECT 
    COUNT(CASE WHEN status = 'approved' THEN 1 END) / COUNT(*) * 100 as approval_rate
FROM manager_request
WHERE reviewed_at IS NOT NULL;
```

### Response Time
```sql
SELECT 
    AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_hours_to_review,
    MAX(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as max_hours,
    MIN(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as min_hours
FROM manager_request
WHERE reviewed_at IS NOT NULL;
```

---

## ✅ Acceptance Criteria Verification

- [x] Player can request manager role
- [x] Request stored with pending status
- [x] Admin notified of new requests
- [x] Admin can approve/reject
- [x] Approved players get ROLE_MANAGER
- [x] Managers can create teams
- [x] Managers can manage budgets
- [x] Players remain players with all features
- [x] Routes are protected
- [x] CSRF tokens present
- [x] Clean UI/UX

---

*Testing Guide Completed: February 21, 2026*
