# 🏆 Tournament Join System - Complete Implementation Guide

## Overview
This system allows players to join pending tournaments by filling out a registration form. Their team's registration is then sent to the admin for approval.

---

## Step 1: Create the TournamentRegistration Entity

**File:** `src/Entity/TournamentRegistration.php`

The entity tracks:
- Which team wants to join which tournament
- The registration form data (motivation, experience level, etc.)
- Status: pending, approved, rejected
- Admin response/notes

**Key Fields:**
- `tournament`: ManyToOne relationship to Tournament
- `team`: ManyToOne relationship to Team
- `player`: ManyToOne relationship to Player (who submitted the request)
- `status`: pending | approved | rejected
- `motivation`: Text field (why they want to join)
- `experienceLevel`: Team experience level
- `previousAchievements`: Past tournament wins
- `adminNotes`: Admin response when approving/rejecting
- `createdAt`, `updatedAt`: Timestamps

**Unique Constraint:** One team can only register once per tournament

---

## Step 2: Update Tournament Entity

**Modifications to `src/Entity/Tournament.php`:**

Add relationship to track registered teams:
```php
#[ORM\OneToMany(mappedBy: 'tournament', targetEntity: TournamentRegistration::class, cascade: ['remove'])]
private Collection $registrations;
```

Add methods to manage registrations:
- `getRegistrations()`
- `addRegistration(TournamentRegistration $registration)`
- `removeRegistration(TournamentRegistration $registration)`
- `getApprovedTeams()` - returns only approved registrations
- `getPendingRegistrations()` - returns only pending registrations

---

## Step 3: Update Team Entity

**Modifications to `src/Entity/Team.php`:**

Add relationship to track tournament registrations:
```php
#[ORM\OneToMany(mappedBy: 'team', targetEntity: TournamentRegistration::class, cascade: ['remove'])]
private Collection $tournamentRegistrations;
```

Add methods:
- `getTournamentRegistrations()`
- `addTournamentRegistration(TournamentRegistration $registration)`
- `removeTournamentRegistration(TournamentRegistration $registration)`
- `isRegisteredInTournament(Tournament $tournament)` - check if team already registered

---

## Step 4: Create TournamentRegistration Repository

**File:** `src/Repository/TournamentRegistrationRepository.php`

Key methods:
```php
// Find registrations pending admin approval
public function findPendingRegistrations();

// Find registrations for a specific tournament
public function findByTournament(Tournament $tournament);

// Find registrations by status
public function findByStatus(string $status);

// Check if team is already registered
public function isTeamRegistered(Team $team, Tournament $tournament): bool;

// Get approved teams for tournament
public function getApprovedTeamsForTournament(Tournament $tournament): array;
```

---

## Step 5: Create the Tournament Registration Form

**File:** `src/Form/TournamentRegistrationType.php`

Form Fields:
- `motivation` - TextareaType (required, min 20 chars, max 500)
- `experienceLevel` - ChoiceType (options: 'Débutant', 'Intermédiaire', 'Pro')
- `previousAchievements` - TextareaType (optional, max 300)
- Submit button "Join Tournament"

---

## Step 6: Update Tournament Controller for Player

**File:** `src/Controller/TournamentController.php`

New Actions:

### a) Show Available Tournaments (for players)
```php
#[Route('/available', name: 'available', methods: ['GET'])]
public function availableTournaments(
    TournamentRepository $tournamentRepo,
    TournamentRegistrationRepository $registrationRepo
): Response
```
- Show only PENDING tournaments
- Display team registration status (already joined, can join, etc.)
- Show tournament details, dates, prize pool, rules

### b) Join Tournament (GET - show form)
```php
#[Route('/{id}/join', name: 'join', methods: ['GET'])]
public function joinTournamentForm(
    int $id,
    TournamentRepository $tournamentRepo,
    TournamentRegistrationRepository $registrationRepo
): Response
```
- Check if tournament exists and is pending
- Check if player's team already registered
- Display the registration form
- Show tournament details and rules

### c) Submit Tournament Registration (POST)
```php
#[Route('/{id}/join', name: 'join_submit', methods: ['POST'])]
public function joinTournamentSubmit(
    int $id,
    Request $request,
    TournamentRepository $tournamentRepo,
    EntityManagerInterface $em,
    TournamentRegistrationRepository $registrationRepo
): Response
```
- Validate tournament exists and is pending
- Get current user's team
- Check if team already registered (prevent duplicates)
- Create TournamentRegistration entity with status = 'pending'
- Save to database
- Send notification to admin
- Redirect to success page

---

## Step 7: Create Admin Tournament Controller

**File:** `src/Controller/AdminTournamentController.php`

New Actions:

### a) View Tournament Registration Requests
```php
#[Route('/admin/tournament-registrations', name: 'admin_tournament_registrations', methods: ['GET'])]
public function listRegistrations(): Response
```
- Show all pending registration requests
- Sortable by tournament, team, date
- Filter by status (pending, approved, rejected)

### b) Review Single Registration
```php
#[Route('/admin/tournament-registrations/{id}', name: 'admin_registration_detail', methods: ['GET'])]
public function reviewRegistration(int $id): Response
```
- Show registration details
- Team info, tournament info
- Player motivation, experience level, achievements
- Display approve/reject options

### c) Approve Registration
```php
#[Route('/admin/tournament-registrations/{id}/approve', 
        name: 'admin_registration_approve', methods: ['POST'])]
public function approveRegistration(int $id, Request $request): Response
```
- Update registration status to 'approved'
- Store optional admin notes
- Send notification to player/team
- Add team to tournament's approved teams list
- Redirect back with success message

### d) Reject Registration
```php
#[Route('/admin/tournament-registrations/{id}/reject', 
        name: 'admin_registration_reject', methods: ['POST'])]
public function rejectRegistration(int $id, Request $request): Response
```
- Update registration status to 'rejected'
- Store rejection reason in adminNotes
- Send notification to player with reason
- Redirect back with success message

---

## Step 8: Create Templates

### a) Player - Browse Pending Tournaments
**File:** `templates/tournament/available.html.twig`

Display:
- List of all pending tournaments
- Tournament name, date range, location, prize pool
- Join button (if not registered)
- "Already joined" badge (if registered)
- View details link

### b) Player - Join Tournament Form
**File:** `templates/tournament/join.html.twig`

Display:
- Tournament details (name, date, location, rules, prize pool)
- Team info (name, country, level, description)
- Registration form:
  - Motivation field
  - Experience level dropdown
  - Previous achievements field
  - Submit button

### c) Admin - View Registration Requests
**File:** `templates/admin/tournament_registrations/list.html.twig`

Display:
- Table of all registrations
- Columns: Tournament, Team, Player, Status, Date, Actions
- Filter by status
- Search by tournament or team name
- Approve/Reject/View buttons

### d) Admin - Review Registration Details
**File:** `templates/admin/tournament_registrations/detail.html.twig`

Display:
- Tournament information
- Team information
- Registration details (motivation, experience, achievements)
- Admin notes field
- Approve with optional notes button
- Reject with reason button
- Cancel button

---

## Step 9: Create Notifications

**Updates to send notifications:**

1. **Player submits registration:**
   - Admin gets notification: "New tournament registration from [Team] for [Tournament]"

2. **Admin approves:**
   - Player gets notification: "Your team [Team] has been approved for [Tournament]!"
   - Include tournament details and what's next

3. **Admin rejects:**
   - Player gets notification: "Your registration for [Tournament] was not approved"
   - Include rejection reason

---

## Step 10: Database Migration

Create migration to add:
1. `tournament_registration` table
2. Foreign keys to tournament, team, player
3. Status enum column
4. Unique constraint on (tournament_id, team_id)
5. Index on (tournament_id, status)
6. Index on (team_id)

---

## Step 11: Security & Validation

**Permission Checks:**

1. **Join Tournament:**
   - User must be authenticated
   - User must be a player (role check)
   - User must be on a team
   - Team must not already be registered
   - Tournament must be pending status

2. **View Registrations (Admin):**
   - User must have ROLE_ADMIN
   - Only admins can view pending registrations

3. **Approve/Reject (Admin):**
   - User must have ROLE_ADMIN
   - Registration must exist
   - Only pending registrations can be acted on

---

## Step 12: User Flow Diagram

```
PLAYER JOURNEY:
├─ Browse pending tournaments (available.twig)
├─ Click "Join Tournament"
├─ Fill registration form:
│  ├─ Motivation (why join)
│  ├─ Experience level
│  └─ Previous achievements
├─ Submit form
└─ See "Pending approval" status

ADMIN JOURNEY:
├─ Dashboard shows new registration requests
├─ Click to review registration details
├─ View team info, motivation, achievements
├─ Choose: Approve or Reject
│  ├─ If Approve: Add team to tournament
│  └─ If Reject: Send reason to player
└─ System sends notifications
```

---

## Step 13: Status Tracking

**Tournament Registration Statuses:**
- `pending` - Waiting for admin review
- `approved` - Admin approved, team can participate
- `rejected` - Admin rejected, team cannot participate

**Flow:**
```
pending --> approved (with potential admin notes)
pending --> rejected (with rejection reason)
```

---

## Step 14: Related Entities Updates

### Team Entity:
- Add collection for tournament registrations
- Add method to check if registered in tournament
- Add method to get active tournament registrations

### Tournament Entity:
- Add collection for registrations
- Add method to get approved teams
- Add method to check if team is approved
- Add method to get pending registrations

### Player Entity:
- Not directly modified (team owns the registration)
- Player is only recording who submitted the request

---

## Step 15: Template Structure Summary

```
templates/
├─ tournament/
│  ├─ available.html.twig (list of pending tournaments)
│  ├─ join.html.twig (join form)
│  └─ join_success.html.twig (confirmation)
│
└─ admin/
   └─ tournament_registrations/
      ├─ list.html.twig (all registrations)
      ├─ detail.html.twig (single registration)
      └─ partials/
         └─ registration_card.html.twig (reusable component)
```

---

## Implementation Priority

1. ✅ Create `TournamentRegistration` entity
2. ✅ Update `Tournament` entity with relationships
3. ✅ Update `Team` entity with relationships
4. ✅ Create `TournamentRegistrationRepository`
5. ✅ Create `TournamentRegistrationType` form
6. ✅ Create player tournament join routes
7. ✅ Create admin review routes
8. ✅ Create templates (player and admin)
9. ✅ Add notifications
10. ✅ Database migration
11. ✅ Test workflow

---

## Key Features

✨ **For Players:**
- Browse all pending tournaments
- Join with detailed motivation form
- Track application status
- Receive notifications on approval/rejection

✨ **For Admins:**
- View all tournament registration requests
- Review team information and motivation
- Approve/reject with notes
- Manage tournament teams

✨ **Security:**
- Only players can join
- Only one registration per team per tournament
- Admin approval required
- Proper permission checks

---

## Success Criteria

- [ ] Player can see list of pending tournaments
- [ ] Player can fill and submit join form
- [ ] Registration status shows as "pending"
- [ ] Admin receives notification
- [ ] Admin can view and approve registrations
- [ ] Player receives approval/rejection notification
- [ ] Approved teams appear in tournament's team list
- [ ] Duplicate registrations are prevented
- [ ] All validations work correctly
