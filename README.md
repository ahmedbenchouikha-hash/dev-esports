# Dev Esports - Tournament Management System

Professional Esports Tournament Management System built with Symfony 7 and Bootstrap 5.

## Features

### ✅ Completed Requirements

#### 1. **Integrated Templates**

- Professional dark-themed UI using Bootstrap 5
- Fully responsive design for desktop and mobile
- Separate Front Office and Back Office interfaces
- Functional navigation between all pages
- Beautiful card-based layouts with hover effects

#### 2. **Entity Management with CRUD**

**Entities Created:**

- **Game** - Tournament matches with scoring
- **Team** - Teams participating in tournaments
- **Player** - Individual players belonging to teams
- **Tournament** - Tournament organization and management

**Key Relationships:**

- Game → Team (Many-to-One) - Game references two teams
- Team → Player (One-to-Many) - Teams have multiple players
- Tournament → Game (One-to-Many) - Tournaments have multiple matches
- Player → Team (Many-to-One) - Players belong to a team

**CRUD Operations:**

- ✓ Create new entities via forms
- ✓ Read/View entity details
- ✓ Update entity information
- ✓ Delete entities with confirmation
- ✓ List views with sorting and filtering

#### 3. **Server-side Input Validation**

**Validation Constraints Applied:**

- Entity-level validation using Symfony's Validator component
- Form-based validation through FormType classes
- No HTML5 or JavaScript validation - all validation is server-side

**Validated Fields:**

- Team: Name (required, 2-255 chars)
- Player: Nickname, FirstName, LastName (required, 2-255 chars)
- Game: Team references, scores (non-negative), dates
- Tournament: Name, dates (start before end), status, prize pool (positive)

#### 4. **Advanced Features**

**Search Functionality:**

- Global search by team name across matches and tournaments
- Player search by nickname, first name, or last name
- Tournament search by name and description
- Real-time filtering without page reload

**Sorting & Filtering:**

- Filter matches by status (pending, ongoing, finished, cancelled)
- Filter tournaments by status
- Sort results by multiple criteria
- Upcoming matches and tournaments views

**Additional Features:**

- Flash messages for user feedback (success, error, info)
- CSRF token protection for form submissions
- Responsive data tables with hover effects
- Team statistics (number of players, matches)
- Player roster views by team
- Match history and upcoming matches

## Project Structure

```
src/
├── Entity/               # Database entities
│   ├── Game.php
│   ├── Team.php
│   ├── Player.php
│   └── Tournament.php
├── Controller/
│   ├── HomeController.php           # Home page
│   ├── MatchController.php          # Front office matches
│   ├── TeamController.php           # Front office teams
│   ├── TournamentController.php     # Front office tournaments
│   ├── MatchAdminController.php     # Back office matches
│   ├── TeamAdminController.php      # Back office teams
│   ├── PlayerAdminController.php    # Back office players
│   └── TournamentAdminController.php # Back office tournaments
├── Form/                # Form types
│   ├── GameType.php
│   ├── TeamType.php
│   ├── PlayerType.php
│   └── TournamentType.php
└── Repository/          # Database queries
    ├── GameRepository.php
    ├── TeamRepository.php
    ├── PlayerRepository.php
    └── TournamentRepository.php

templates/
├── base.html.twig       # Base template with navigation
├── home.html.twig       # Home page
├── match/               # Front office match templates
│   ├── index.html.twig
│   ├── show.html.twig
│   └── upcoming.html.twig
├── team/                # Front office team templates
│   ├── index.html.twig
│   └── show.html.twig
├── tournament/          # Front office tournament templates
│   ├── index.html.twig
│   ├── show.html.twig
│   └── upcoming.html.twig
└── admin/               # Back office admin templates
    ├── match/
    ├── team/
    ├── player/
    └── tournament/
```

## Routes Overview

### Front Office (Public)

- `/` - Home page
- `/matches` - All matches with search/filter
- `/matches/upcoming` - Upcoming matches
- `/matches/{id}` - Match details
- `/teams` - All teams with search
- `/teams/{id}` - Team details with players
- `/tournaments` - All tournaments with search/filter
- `/tournaments/upcoming` - Upcoming tournaments
- `/tournaments/{id}` - Tournament details with matches

### Back Office (Admin)

- `/admin/matches` - Manage matches (CRUD)
- `/admin/matches/create` - Create new match
- `/admin/matches/{id}` - View match
- `/admin/matches/{id}/edit` - Edit match
- `/admin/matches/{id}/delete` - Delete match
- `/admin/teams` - Manage teams (CRUD)
- `/admin/players` - Manage players (CRUD)
- `/admin/tournaments` - Manage tournaments (CRUD)

## Installation & Setup

### Prerequisites

- PHP 8.2+
- Symfony CLI
- Composer
- MySQL/MariaDB

### Installation Steps

1. **Install dependencies:**

```bash
composer install
```

2. **Create database:**

```bash
php bin/console doctrine:database:create
```

3. **Run migrations:**

```bash
php bin/console doctrine:migrations:migrate
```

4. **Create fixtures (optional):**

```bash
php bin/console doctrine:fixtures:load
```

5. **Run development server:**

```bash
symfony serve
```

Access the application at `http://localhost:8000`

## Form Validation Examples

### Game Form Validation

```php
- Team 1: Required entity selection
- Team 2: Required entity selection
- Score 1: Non-negative integer (>= 0)
- Score 2: Non-negative integer (>= 0)
- Match Date: Required datetime
- Status: Must be one of [pending, ongoing, finished, cancelled]
- Tournament: Required entity selection
```

### Team Form Validation

```php
- Name: Required, 2-255 characters
- Country: Optional, max 255 characters
- Description: Optional text field
```

### Player Form Validation

```php
- Nickname: Required, 2-255 characters
- First Name: Required, 2-255 characters
- Last Name: Required, 2-255 characters
- Birth Date: Optional, must be in the past
- Role: Optional text (e.g., ADC, Support, Mid)
- Team: Required entity selection
```

### Tournament Form Validation

```php
- Name: Required, 2-255 characters
- Description: Optional text
- Start Date: Required, must be before end date
- End Date: Required
- Status: Must be one of [pending, ongoing, completed, cancelled]
- Location: Optional text
- Prize Pool: Optional, must be positive if provided
```

## Advanced Repository Methods

### GameRepository

- `findByStatus(string $status)` - Filter by match status
- `findByTournament(Tournament $tournament)` - Get matches for a tournament
- `findBySearchTerm(string $searchTerm)` - Search matches by team name
- `findUpcoming()` - Get upcoming matches
- `findAllOrdered(string $orderBy)` - Sort matches

### TeamRepository

- `findBySearchTerm(string $searchTerm)` - Search teams
- `findAllOrdered(string $orderBy)` - Sort teams

### PlayerRepository

- `findBySearchTerm(string $searchTerm)` - Search players
- `findByTeam(Team $team)` - Get players for a team
- `findAllOrdered(string $orderBy)` - Sort players

### TournamentRepository

- `findBySearchTerm(string $searchTerm)` - Search tournaments
- `findByStatus(string $status)` - Filter by status
- `findUpcoming()` - Get upcoming tournaments
- `findAllOrdered(string $orderBy)` - Sort tournaments

## Styling Features

- **Color Scheme**: Dark theme with accent color (#e94560)
- **Responsive Grid**: Bootstrap 5 responsive layout
- **Cards**: Hover effects on interactive elements
- **Forms**: Custom styled inputs with validation feedback
- **Tables**: Striped rows with hover highlighting
- **Badges**: Status indicators with color coding
- **Modals**: Bootstrap modals for confirmation dialogs
- **Navigation**: Sticky navbar with dropdown menus
- **Footer**: Fixed footer with copyright information

## Security Features

- CSRF token protection on all forms
- Secure entity binding with Symfony's ParamConverter
- Proper exception handling for missing entities
- Input validation on all forms
- No sensitive data in URLs

## Future Enhancements

- API endpoints for tournament data
- User authentication and authorization
- Role-based access control (Admin/User)
- File uploads for team logos
- Email notifications
- Export to PDF/Excel
- Real-time match updates
- Advanced statistics and analytics
- Mobile app API

## License

This project is part of PIDEV course work.

## Author

Ahmed - Dev Esports Team
