# RankUp - Project Architecture Overview

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     USER INTERFACE LAYER                     │
│  ┌──────────────────────────────────────────────────────┐   │
│  │           Base Template (base.html.twig)             │   │
│  │   - Navigation Bar (Front Office + Admin links)      │   │
│  │   - Responsive Bootstrap 5 Layout                    │   │
│  │   - Flash Messages & Footer                          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    FRONT OFFICE (Public)                     │
├──────────────┬──────────────┬────────────────┬─────────────┤
│   Matches    │    Teams     │  Tournaments   │    Home     │
│              │              │                │             │
│ - Index      │ - Index      │ - Index        │ - Dashboard │
│ - Show       │ - Show       │ - Show         │             │
│ - Upcoming   │              │ - Upcoming     │             │
└──────────────┴──────────────┴────────────────┴─────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                  BACK OFFICE (Administration)                │
├──────────────┬──────────────┬────────────────┬─────────────┤
│   Matches    │    Teams     │    Players     │ Tournaments │
│              │              │                │             │
│ - Create     │ - Create     │ - Create       │ - Create    │
│ - Read       │ - Read       │ - Read         │ - Read      │
│ - Update     │ - Update     │ - Update       │ - Update    │
│ - Delete     │ - Delete     │ - Delete       │ - Delete    │
│ - Search     │ - Search     │ - Search       │ - Search    │
│ - Filter     │              │                │ - Filter    │
└──────────────┴──────────────┴────────────────┴─────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   CONTROLLER LAYER (8 Controllers)          │
├──────────────┬──────────────┬────────────────┬─────────────┤
│ MatchCtr     │ TeamCtr      │ HomeCtr        │             │
│ (FO)         │ (FO)         │                │             │
├──────────────┼──────────────┼────────────────┼─────────────┤
│ MatchAdmCtr  │ TeamAdmCtr   │ PlayerAdmCtr   │ TournAdmCtr │
│ (BO)         │ (BO)         │ (BO)           │ (BO)        │
└──────────────┴──────────────┴────────────────┴─────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    FORM LAYER (4 Form Types)                │
│  ┌──────────┬──────────┬──────────┬──────────────────┐     │
│  │ GameType │ TeamType │PlayerType│ TournamentType   │     │
│  │          │          │          │                  │     │
│  │ Validates│ Validates│ Validates│ Validates        │     │
│  │ on Submit│ on Submit│ on Submit│ on Submit        │     │
│  └──────────┴──────────┴──────────┴──────────────────┘     │
└─────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                  REPOSITORY LAYER (4 Repositories)          │
├──────────────┬──────────────┬────────────────┬─────────────┤
│ GameRepository  - findByStatus()    - findUpcoming()      │
│ TeamRepository  - findBySearchTerm() - findAllOrdered()   │
│ PlayerRepository - findByTeam()      - findBySearchTerm()  │
│ TournamentRepository - findByStatus()- findUpcoming()     │
└──────────────┬──────────────┬────────────────┬─────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   ENTITY LAYER (4 Entities)                 │
├──────────────┬──────────────┬────────────────┬─────────────┤
│   Game       │    Team      │    Player      │ Tournament  │
├──────────────┼──────────────┼────────────────┼─────────────┤
│ id           │ id           │ id             │ id          │
│ team1*       │ name         │ nickname       │ name        │
│ team2*       │ country      │ firstName      │ description │
│ score1       │ description  │ lastName       │ startDate   │
│ score2       │ createdAt    │ birthDate      │ endDate     │
│ matchdate    │ updatedAt    │ role           │ status      │
│ status       │ players []   │ team*          │ location    │
│ tournament*  │ games []     │ createdAt      │ prizePool   │
│ createdAt    │              │ updatedAt      │ games []    │
│ updatedAt    │              │                │ createdAt   │
│              │              │                │ updatedAt   │
└──────────────┴──────────────┴────────────────┴─────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE LAYER (MySQL)                   │
│  ┌──────────┬──────────┬──────────┬──────────────────┐     │
│  │ game     │ team     │ player   │ tournament       │     │
│  │ table    │ table    │ table    │ table            │     │
│  └──────────┴──────────┴──────────┴──────────────────┘     │
└─────────────────────────────────────────────────────────────┘

* = Foreign Key Relationship
[] = One-to-Many Collection
```

---

## Entity Relationship Diagram

```
                           ┌─────────────────┐
                           │   Tournament    │
                           │  (1 Tournament) │
                           └────────┬────────┘
                                    │
                                    │ 1-to-Many
                                    │
                           ┌────────▼────────┐
                           │      Game       │
                           │   (N Matches)   │
                           └────────┬────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    │ Many-to-One   │ Many-to-One   │
                    │               │               │
              ┌─────▼──────┐   ┌────▼──────┐       │
              │    Team    │   │   Team    │       │
              │  (Team 1)  │   │  (Team 2) │       │
              └─────┬──────┘   └────┬──────┘       │
                    │                │             │
                    │ 1-to-Many     │ 1-to-Many   │
                    │                │             │
                    │                │             │
                    └────────┬───────┘             │
                             │                    │
                      ┌──────▼──────┐             │
                      │   Player    │◄────────────┘
                      │ (Team Squad)│
                      └─────────────┘
```

---

## Data Flow Example: Creating a Match

```
1. User navigates to: /admin/matches/create
                           ▼
2. MatchAdminController::create() loads
                           ▼
3. Creates new Game() entity
                           ▼
4. Creates GameType form with validation rules
                           ▼
5. Renders admin/match/form.html.twig with form
                           ▼
6. User fills form:
   - Selects Team 1 (validates: required)
   - Selects Team 2 (validates: required)
   - Enters Score 1 (validates: >= 0)
   - Enters Score 2 (validates: >= 0)
   - Selects Status (validates: choice)
   - Selects Tournament (validates: required)
   - Selects Match Date (validates: valid datetime)
                           ▼
7. User clicks "Create Match"
                           ▼
8. Form submission → GameType validates all fields
                           ▼
9. If validation fails:
   - Render form again with error messages
   - User sees: "Score must be positive" etc.
                           ▼
10. If validation succeeds:
    - EntityManager persist(game)
    - EntityManager flush()
    - INSERT INTO game (...) VALUES (...)
                           ▼
11. Flash message: "Match created successfully!"
                           ▼
12. Redirect to: /admin/matches/{id}
```

---

## Search & Filter Flow

```
User Input (List Page)
        ▼
GET /matches?search=Team&status=finished
        ▼
MatchController::index()
        ▼
Check Query Parameters
        ▼
┌─────────────────────────────────────────┐
│ if search param exists:                 │
│   GameRepository::findBySearchTerm()    │
│                                         │
│ else if status param exists:            │
│   GameRepository::findByStatus()        │
│                                         │
│ else:                                   │
│   GameRepository::findAllOrdered()      │
└─────────────────────────────────────────┘
        ▼
Return filtered results
        ▼
Render template with filters applied
        ▼
Display results with current filters highlighted
```

---

## Directory Structure

```
dev-esports/
│
├── src/
│   ├── Controller/               # 8 Controllers
│   │   ├── HomeController.php
│   │   ├── MatchController.php
│   │   ├── MatchAdminController.php
│   │   ├── TeamController.php
│   │   ├── TeamAdminController.php
│   │   ├── PlayerAdminController.php
│   │   ├── TournamentController.php
│   │   └── TournamentAdminController.php
│   │
│   ├── Entity/                   # 4 Entities
│   │   ├── Game.php
│   │   ├── Team.php
│   │   ├── Player.php
│   │   └── Tournament.php
│   │
│   ├── Form/                     # 4 Form Types
│   │   ├── GameType.php
│   │   ├── TeamType.php
│   │   ├── PlayerType.php
│   │   └── TournamentType.php
│   │
│   ├── Repository/               # 4 Repositories
│   │   ├── GameRepository.php
│   │   ├── TeamRepository.php
│   │   ├── PlayerRepository.php
│   │   └── TournamentRepository.php
│   │
│   └── Kernel.php
│
├── templates/
│   ├── base.html.twig            # Master template
│   ├── home.html.twig
│   │
│   ├── match/                    # Front Office - 3 templates
│   │   ├── index.html.twig
│   │   ├── show.html.twig
│   │   └── upcoming.html.twig
│   │
│   ├── team/                     # Front Office - 2 templates
│   │   ├── index.html.twig
│   │   └── show.html.twig
│   │
│   ├── tournament/               # Front Office - 3 templates
│   │   ├── index.html.twig
│   │   ├── show.html.twig
│   │   └── upcoming.html.twig
│   │
│   └── admin/                    # Back Office - 12 templates
│       ├── match/
│       │   ├── index.html.twig
│       │   ├── show.html.twig
│       │   └── form.html.twig
│       ├── team/
│       │   ├── index.html.twig
│       │   ├── show.html.twig
│       │   └── form.html.twig
│       ├── player/
│       │   ├── index.html.twig
│       │   ├── show.html.twig
│       │   └── form.html.twig
│       └── tournament/
│           ├── index.html.twig
│           ├── show.html.twig
│           └── form.html.twig
│
├── config/
│   ├── bundles.php
│   ├── routes.yaml
│   ├── services.yaml
│   └── packages/
│       ├── doctrine.yaml
│       ├── framework.yaml
│       └── twig.yaml
│
├── public/
│   └── index.php                 # Entry point
│
├── migrations/                   # Database migrations
│
├── .env                          # Environment variables
├── composer.json
├── symfony.lock
│
├── README.md                     # Full documentation
├── IMPLEMENTATION_SUMMARY.md     # Summary of features
└── QUICK_REFERENCE.md           # Commands & URLs
```

---

## Validation Flow

```
User Submit Form
        ▼
FormType::buildForm() builds form structure
        ▼
Form::handleRequest() processes submission
        ▼
Form::isSubmitted() && Form::isValid()
        ▼
Symfony Validator checks constraints:
├── @Assert\NotBlank
├── @Assert\Length
├── @Assert\GreaterThanOrEqual
├── @Assert\LessThan
├── @Assert\Choice
└── @Assert\Positive
        ▼
┌─────────────────────────┐
│ Validation Failed?      │
├─────────────────────────┤
│ YES → Show form + errors│
│ NO  → Save to database  │
└─────────────────────────┘
        ▼
Flash message to user
```

---

## Technology Stack

```
Frontend:
├── Bootstrap 5 (CSS Framework)
├── Twig (Template Engine)
├── HTML5
└── CSS3 (Custom Dark Theme)

Backend:
├── Symfony 7 (Web Framework)
├── PHP 8.2+ (Language)
├── Doctrine ORM (Database)
└── Form Component

Database:
├── MySQL/MariaDB
├── Doctrine Migrations
└── 4 Main Tables

Security:
├── CSRF Protection
├── Input Validation
├── SQL Injection Prevention
└── Secure Form Handling
```

---

## Key Metrics

- **4** Entities
- **8** Controllers (4 Front + 4 Back)
- **4** Form Types
- **4** Repositories
- **20** Templates
- **50+** Routes
- **100%** Server-side Validation
- **1** Base Template
- **Full** CRUD Operations
- **Responsive** Design (Mobile-friendly)

---

Generated: February 7, 2026
PIDEV - Dev Esports Tournament Management System

> > > > > > > module-tournament
