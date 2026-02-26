# 🏗️ Tournament Join System - Architecture & Flow Diagrams

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     TOURNAMENT JOIN SYSTEM                       │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐         ┌──────────────────────┐
│                      │         │                      │
│   PLAYER SIDE        │         │   ADMIN SIDE         │
│                      │         │                      │
│ • Browse Tournaments │         │ • Review Requests    │
│ • Join Tournament    │         │ • Approve/Reject     │
│ • Track Status       │         │ • Manage Teams       │
│                      │         │                      │
└──────────┬───────────┘         └──────────┬───────────┘
           │                                  │
           │                                  │
           └──────────────┬───────────────────┘
                          │
                ┌─────────▼─────────┐
                │  DATABASE MODEL   │
                │                   │
                │ tournament_       │
                │ registration      │
                │                   │
                │ status: pending   │
                │         approved  │
                │         rejected  │
                └─────────┬─────────┘
                          │
                ┌─────────▼──────────────────┐
                │   HELPER SERVICES          │
                │                            │
                │ • Validation               │
                │ • Duplicate Prevention     │
                │ • Status Management        │
                │ • Snapshot Tracking        │
                └────────────────────────────┘
```

---

## Database Schema

```
TOURNAMENT TABLE
    ├─ id (PK)
    ├─ name
    ├─ status: pending|ongoing|completed|cancelled
    ├─ startDate
    ├─ endDate
    ├─ location
    ├─ prizePool
    └─ (many other fields)
             ▲
             │ OneToMany
             │
TOURNAMENT_REGISTRATION TABLE
    ├─ id (PK)
    ├─ tournament_id (FK) ────────────► TOURNAMENT
    ├─ team_id (FK) ───────────────────► TEAM
    ├─ player_id (FK) ──────────────────► USER (Player)
    ├─ status: pending|approved|rejected
    ├─ motivation (TEXT)
    ├─ experienceLevel: Débutant|Intermédiaire|Pro
    ├─ previousAchievements (TEXT)
    ├─ adminNotes (TEXT)
    ├─ createdAt (TIMESTAMP)
    ├─ updatedAt (TIMESTAMP)
    └─ reviewedAt (TIMESTAMP, nullable)
             │
             │ ManyToOne
             ▼
         TEAM TABLE
    ├─ id (PK)
    ├─ name
    ├─ country
    ├─ niveau: Débutant|Intermédiaire|Pro
    ├─ jeu: LoL|CS:GO|Dota 2|FIFA
    └─ (other team fields)

KEY CONSTRAINTS:
    UNIQUE(tournament_id, team_id) ◄── One team per tournament
    INDEX(tournament_id, status)    ◄── Fast filtering
    INDEX(team_id)                  ◄── Fast lookups
```

---

## Request-Response Flow

### Player Joins Tournament

```
PLAYER                          CONTROLLER                      DATABASE
  │                                │                               │
  ├─ GET /tournaments/available    │                               │
  ├───────────────────────────────►│                               │
  │                                ├─ findByStatus('pending')      │
  │                                ├──────────────────────────────►│
  │                                │◄──────── tournaments ────────┤
  │                            RENDER available.html.twig         │
  │◄─ Show Pending Tournaments ────┤                               │
  │                                │                               │
  ├─ Click "Join Tournament" ─────►│                               │
  │  GET /tournaments/{id}/join    │                               │
  │                                ├─ Check tournament exists      │
  │                                ├─ Check tournament is pending  │
  │                                ├─ Check player has team       │
  │                                ├─ Check not already registered│
  │                            RENDER join_form.html.twig         │
  │◄─ Show Join Form ──────────────┤                               │
  │                                │                               │
  ├─ Fill Form & Submit ──────────►│                               │
  │  POST /tournaments/{id}/join   │                               │
  │                                ├─ Validate form               │
  │                                ├─ Create TournamentRegistration
  │                                ├─ Set status = 'pending'      │
  │                                ├────────────────────────────────► INSERT
  │                                │                               │
  │◄─ Success! Pending Approval ───┤                               │
  │                                │                               │
  ├─ GET /tournaments/my-regs ────►│                               │
  │                                ├─ Get team registrations      │
  │                                ├──────────────────────────────►│ SELECT
  │◄─ Show Registrations ──────────┤◄────── registrations ────────┤
```

---

### Admin Reviews Registration

```
ADMIN                          CONTROLLER                      DATABASE
  │                                │                               │
  ├─ GET /admin/tournament-regs   │                               │
  ├───────────────────────────────►│                               │
  │                                ├─ findPendingRegistrations()  │
  │                                ├──────────────────────────────►│
  │                                │◄──── pending registrations ──┤
  │                            RENDER list.html.twig              │
  │◄─ Show List ───────────────────┤                               │
  │                                │                               │
  ├─ Click "Review" ─────────────►│                               │
  │  GET /admin/tournament-regs/{id}                              │
  │                                ├─ Find registration           │
  │                                ├──────────────────────────────►│ SELECT
  │                                │◄────── registration ─────────┤
  │                            RENDER show.html.twig              │
  │◄─ Show Details ────────────────┤                               │
  │  (Tournament, Team, Form Data)  │                               │
  │                                │                               │
  ├─ Make Decision ───────────────►│                               │
  │                                │                               │
  │─ Option A: APPROVE ───────────►│                               │
  │  POST /admin/..../approve      │                               │
  │                                ├─ Update status = 'approved'  │
  │                                ├─ Set reviewedAt timestamp    │
  │                                ├─ Store admin notes           │
  │                                ├────────────────────────────────► UPDATE
  │                                │                               │
  │◄─ Success! Approved ───────────┤                               │
  │                                │                               │
  │─ Option B: REJECT ────────────►│                               │
  │  POST /admin/..../reject       │                               │
  │                                ├─ Update status = 'rejected'  │
  │                                ├─ Set reviewedAt timestamp    │
  │                                ├─ Store rejection reason      │
  │                                ├────────────────────────────────► UPDATE
  │                                │                               │
  │◄─ Success! Rejected ───────────┤                               │
```

---

## Entity Relationship Diagram

```
                        ┌───────────────────┐
                        │   TOURNAMENT      │
                        ├───────────────────┤
                        │ - id (PK)         │
                        │ - name            │
                        │ - status          │
                        │ - startDate       │
                        │ - endDate         │
                        │ - location        │
                        │ - prizePool       │
                        │ - rules           │
                        └────────┬──────────┘
                                 │
                    OneToMany ◄───┴───► ManyToOne
                                 │
                        ┌────────▼──────────┐
                        │ TOURNAMENT_       │
                        │ REGISTRATION      │
                        ├───────────────────┤
                        │ - id (PK)         │
                        │ - tournament_id   │◄──┐
                        │ - team_id         │   │
                        │ - player_id       │   │
                        │ - status          │   │
                        │ - motivation      │   │
                        │ - experience_lvl  │   │
                        │ - achievements    │   │
                        │ - admin_notes     │   │
                        │ - createdAt       │   │
                        │ - reviewedAt      │   │
                        └────┬────────┬─────┘   │
                             │        │        │
        ManyToOne ◄──────────┴────┐   │        │
                             │    │   │        │
                             ▼    │   │        │
                      ┌───────────┴──┐│        │
                      │     TEAM    │ │        │
                      ├─────────────┐ │        │
                      │ - id (PK)   │ │        │
                      │ - name      │ │        │
                      │ - country   │ │        │
                      │ - niveau    │ │        │
                      │ - jeu       │ │        │
                      │ - players   │ │        │
                      └─────────────┘ │        │
                                     │        │
                                     │        │
                ManyToOne ◄──────────┘        │
                                     │        │
                                     ▼        │
                              ┌──────────────┴──┐
                              │     USER       │
                              ├────────────────┤
                              │ - id (PK)      │
                              │ - email        │
                              │ - password     │
                              │ - roles        │
                              │ - nickname     │
                              │ - team (FK)    │
                              └────────────────┘

RELATIONSHIP SUMMARY:
- Tournament has many TournamentRegistrations (OneToMany)
- TournamentRegistration references one Tournament (ManyToOne)
- TournamentRegistration references one Team (ManyToOne)
- TournamentRegistration references one Player/User (ManyToOne)
- Team has many TournamentRegistrations (OneToMany)
- Player has many TournamentRegistrations (OneToMany) - implicit

CONSTRAINTS:
✓ Unique(tournament_id, team_id) - One registration per team per tournament
✓ Cascade Delete - If tournament deleted, registrations deleted
✓ Cascade Delete - If team deleted, registrations deleted
✓ Cascade Delete - If player deleted, registrations deleted
```

---

## Form Processing Pipeline

```
INPUT                VALIDATION              PROCESSING              OUTPUT
  │                      │                         │                    │
  ├─ User fills form     │                         │                    │
  ├─ motivation (1000)   │                         │                    │
  ├─ experience_level    │                         │                    │
  └─ achievements        │                         │                    │
        │                │                         │                    │
        ├──────────────► │ Check Required?         │                    │
        │                ├─ motivation: YES ◄─────┤                    │
        │                ├─ experience: YES ◄─────┤                    │
        │                ├─ achievements: NO ◄────┤                    │
        │                │                         │                    │
        │                ├─ Check Min Length?      │                    │
        │                ├─ motivation ≥ 20 ✓ ◄───┤                    │
        │                │                         │                    │
        │                ├─ Check Max Length?      │                    │
        │                ├─ motivation ≤ 1000 ✓ ◄──┤                    │
        │                ├─ achievements ≤ 500 ✓ ◄──┤                    │
        │                │                         │                    │
        │                ├─ Validate Choices?      │                    │
        │                ├─ experience_level in    │                    │
        │                │  [Débutant, Intermed.,  │                    │
        │                │   Pro] ? ✓ ◄─────────────┤                    │
        │                │                         │                    │
        │                ├─ All Valid? ✓           │                    │
        │                └──────────────┬──────────►│                    │
        │                               │          │ Create Entity      │
        │                               │          ├─ Set Tournament   │
        │                               │          ├─ Set Team         │
        │                               │          ├─ Set Player       │
        │                               │          ├─ Set Status       │
        │                               │          ├─ Add Timestamps   │
        │                               │          │                   │
        │                               │          ├─────────────────► │ Success!
        │                               │          │  INSERT to DB     │
        │                               │          │                   │ Flash msg
        │                               │          │                   │ Redirect
        │                               │          │                   │
        │ (If Validation Fails)         │          │                   │
        │                               │          │                   │
        └────────────────────────────── ├──────────┼──────────────────► │ Show Form
                                 Errors │          │  With Error Msgs  │ Again
```

---

## State Machine (Registration Lifecycle)

```
           ┌─────────────────────────────┐
           │                             │
           │    PLAYER JOINS TOURNAMENT  │
           │     (Fills Form & Posts)    │
           │                             │
           └──────────────┬──────────────┘
                          │
            ┌─────────────▼──────────────┐
            │   NEW REGISTRATION CREATED │
            │   Status: PENDING          │
            │   CreatedAt: NOW           │
            │                            │
            │   Waiting for Admin Review │
            └─────────────┬──────────────┘
                          │
           ┌──────────────┴──────────────┐
           │                             │
     ┌─────▼─────┐              ┌────────▼──────┐
     │  APPROVED │              │   REJECTED    │
     │ ---------- │              │  --------- │
     │ Admin      │              │ Admin      │
     │ Reviewed & │              │ Reviewed & │
     │ Approved   │              │ REJECTED   │
     │            │              │            │
     │ Update:   │              │ Update:   │
     │ • status  │              │ • status   │
     │ • notes   │              │ • reason   │
     │ • time    │              │ • time     │
     │            │              │            │
     └────────────┘              └────────────┘
           │                             │
           │                             │
     ┌─────▼─────┐              ┌────────▼──────┐
     │ Team can  │              │ Team cannot  │
     │ participate│              │ participate  │
     │ in        │              │ in          │
     │ Tournament│              │ Tournament  │
     └───────────┘              └─────────────┘

Key Timestamps:
- createdAt: When registration submitted
- updatedAt: Last update time
- reviewedAt: When admin made decision
```

---

## Controller Action Flow

```
TournamentPlayerController

availableTournaments()
    ├─ Get current user (authenticated: ROLE_PLAYER)
    ├─ Get user's team
    ├─ Find all tournaments with status = 'pending'
    ├─ Build registration status map
    └─ Render available.html.twig

joinTournamentForm()
    ├─ Find tournament by ID
    ├─ Check tournament status = 'pending'
    ├─ Get user's team
    ├─ Check team exists
    ├─ Check not already registered
    ├─ Create new TournamentRegistration
    ├─ Create form
    └─ Render join_form.html.twig

joinTournamentSubmit()
    ├─ Find tournament by ID
    ├─ Validate tournament status = 'pending'
    ├─ Get user's team
    ├─ Check team exists
    ├─ Check not already registered
    ├─ Create form & handle request
    ├─ If valid:
    │   ├─ Set tournament
    │   ├─ Set team
    │   ├─ Set player
    │   ├─ Set status = 'pending'
    │   ├─ Persist to database
    │   ├─ Flash success message
    │   └─ Redirect to available
    └─ If invalid: Show form with errors

myRegistrations()
    ├─ Get current user (authenticated: ROLE_PLAYER)
    ├─ Get user's team
    ├─ If team exists:
    │   └─ Find all registrations for team
    ├─ Else: Return empty list
    └─ Render my_registrations.html.twig


AdminTournamentRegistrationController

list()
    ├─ Get status filter (optional)
    ├─ Get search query (optional)
    ├─ If status: Find by status
    ├─ Else: Find all pending
    ├─ If search: Filter results
    └─ Render list.html.twig

show()
    ├─ Find registration by ID
    ├─ If not found: 404 error
    └─ Render show.html.twig

approve()
    ├─ Find registration by ID
    ├─ Check is pending status
    ├─ Set status = 'approved'
    ├─ Set reviewedAt = NOW
    ├─ Set adminNotes from request
    ├─ Flush to database
    ├─ Flash success message
    └─ Redirect to list

reject()
    ├─ Find registration by ID
    ├─ Check is pending status
    ├─ Get rejection reason from form
    ├─ Set status = 'rejected'
    ├─ Set reviewedAt = NOW
    ├─ Set adminNotes = "Rejection Reason: {reason}"
    ├─ Flush to database
    ├─ Flash success message
    └─ Redirect to list
```

---

## Data Validation Layers

```
LAYER 1: FORM VALIDATION (Client + Server)
    ├─ Motivation
    │  ├─ Required: YES
    │  ├─ Min Length: 20
    │  ├─ Max Length: 1000
    │  └─ Type: String
    │
    ├─ Experience Level
    │  ├─ Required: YES
    │  ├─ Choices: ['Débutant', 'Intermédiaire', 'Pro']
    │  └─ Type: String
    │
    └─ Previous Achievements
       ├─ Required: NO
       ├─ Max Length: 500
       └─ Type: String

LAYER 2: ENTITY VALIDATION (Doctrine)
    ├─ TournamentRegistration
    │  ├─ tournament: NotNull
    │  ├─ team: NotNull
    │  ├─ player: NotNull
    │  ├─ status: Choice (pending|approved|rejected)
    │  ├─ motivation: NotBlank, Length(20-1000)
    │  ├─ experienceLevel: Choice
    │  └─ previousAchievements: Length(max 500)
    │
    ├─ Unique Constraint
    │  └─ UNIQUE(tournament_id, team_id)
    │
    └─ Foreign Keys
       ├─ tournament_id → tournament(id)
       ├─ team_id → team(id)
       └─ player_id → user(id)

LAYER 3: BUSINESS LOGIC VALIDATION (Controller)
    ├─ Tournament must exist
    ├─ Tournament must be 'pending' status
    ├─ Player must be authenticated
    ├─ Player must have a team
    ├─ Team must not already be registered
    ├─ Registration must exist (for approve/reject)
    ├─ Registration must be 'pending' (for approve/reject)
    └─ User must be ROLE_ADMIN (for admin routes)

LAYER 4: DATA INTEGRITY (Database)
    ├─ Primary Key uniqueness
    ├─ Foreign Key constraints
    ├─ Cascade deletes
    ├─ Index efficiency
    └─ Transaction support
```

---

## Security & Permissions

```
AUTHENTICATION REQUIRED:
┌─────────────────────────────────┐
│ All Routes Protected            │
├─────────────────────────────────┤
│ Player Routes: @IsGranted('ROLE_PLAYER')
│ Admin Routes:  @IsGranted('ROLE_ADMIN')
└─────────────────────────────────┘

AUTHORIZATION CHECKS:
┌─────────────────────────────────┐
│ Business Logic Level            │
├─────────────────────────────────┤
│ • Tournament exists?
│ • Tournament status = pending?
│ • Player has team?
│ • Team not already registered?
│ • Registration exists?
│ • Registration is pending?
└─────────────────────────────────┘

DATA PROTECTION:
┌─────────────────────────────────┐
│ Database Level                  │
├─────────────────────────────────┤
│ • Unique constraint prevents duplicates
│ • Foreign keys ensure referential integrity
│ • Cascade delete prevents orphaned records
│ • Timestamps track modifications
│ • Role-based access via @IsGranted
└─────────────────────────────────┘
```

---

## Complete System Overview

```
USER TYPES:
┌─────────────┐         ┌──────────┐
│   PLAYER    │         │  ADMIN   │
├─────────────┤         ├──────────┤
│ Can:        │         │  Can:    │
│ • Browse    │         │ • Review │
│ • Join      │         │ • Approve│
│ • Track     │         │ • Reject │
│ • View own  │         │ • Filter │
└─────────────┘         └──────────┘

REGISTRATION FLOW:
1. Player submits registration
   ├─ Form validation ✓
   ├─ Business logic check ✓
   └─ Database insert ✓

2. Admin receives notification
   ├─ Sees in list
   ├─ Views details
   └─ Makes decision

3. Status updates
   ├─ Approved → Team can play
   ├─ Rejected → Team cannot play
   └─ Pending → Awaiting decision

NOTIFICATIONS (Future):
├─ Player: Registration received
├─ Admin: New request waiting
├─ Player: Approved ✓
├─ Player: Rejected ✗
└─ Admin: Summary report
```

---

## Performance Optimizations

```
DATABASE INDEXES:
┌─────────────────────────────────┐
│ tournament_registration         │
├─────────────────────────────────┤
│ PRIMARY KEY (id)
│ UNIQUE KEY (tournament_id, team_id)
│ INDEX (tournament_id, status)  ◄── Fast filtering
│ INDEX (team_id)                ◄── Fast lookups
└─────────────────────────────────┘

QUERY OPTIMIZATION:
├─ JOIN tournament on registrations
├─ JOIN team on registrations
├─ Selective field selection
├─ Lazy loading for related entities
└─ Pagination for large result sets

CACHE CONSIDERATIONS:
├─ Tournament list (invalidate when tournament changes)
├─ Team registration status (invalidate when status changes)
└─ Admin statistics (invalidate when registration changes)
```

---

This comprehensive architecture ensures the system is:
✅ Scalable
✅ Maintainable
✅ Secure
✅ Performant
✅ User-friendly
