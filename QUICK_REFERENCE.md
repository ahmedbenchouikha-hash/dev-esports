# Quick Reference Guide

## Commands to Run Your Project

### 1. **Initial Setup**

```bash
cd "c:\Users\ahmed\OneDrive\Bureau\dev esports\dev-esports"
composer install
```

### 2. **Database Setup**

```bash
# Create database
php bin/console doctrine:database:create

# Generate migration from entities
php bin/console make:migration

# Apply migration
php bin/console doctrine:migrations:migrate

# View database schema
php bin/console doctrine:schema:validate
```

### 3. **Load Sample Data (Optional)**

Create a fixture to test the application:

```bash
php bin/console make:fixtures GameFixtures
# Then implement fixture loading
php bin/console doctrine:fixtures:load
```

### 4. **Run Development Server**

```bash
# Option 1: Using Symfony CLI
symfony serve

# Option 2: Using PHP
php -S localhost:8000 -t public/

# Application will be available at http://localhost:8000
```

### 5. **Clear Cache (if needed)**

```bash
php bin/console cache:clear
```

---

## Application URLs

### 🌐 Front Office (Public)

- **Home**: http://localhost:8000/
- **Matches**: http://localhost:8000/matches
- **Upcoming Matches**: http://localhost:8000/matches/upcoming
- **Match Details**: http://localhost:8000/matches/{id}
- **Teams**: http://localhost:8000/teams
- **Team Details**: http://localhost:8000/teams/{id}
- **Tournaments**: http://localhost:8000/tournaments
- **Upcoming Tournaments**: http://localhost:8000/tournaments/upcoming
- **Tournament Details**: http://localhost:8000/tournaments/{id}

### ⚙️ Back Office (Admin)

- **Manage Matches**: http://localhost:8000/admin/matches
- **Create Match**: http://localhost:8000/admin/matches/create
- **Edit Match**: http://localhost:8000/admin/matches/{id}/edit
- **Manage Teams**: http://localhost:8000/admin/teams
- **Create Team**: http://localhost:8000/admin/teams/create
- **Edit Team**: http://localhost:8000/admin/teams/{id}/edit
- **Manage Players**: http://localhost:8000/admin/players
- **Create Player**: http://localhost:8000/admin/players/create
- **Edit Player**: http://localhost:8000/admin/players/{id}/edit
- **Manage Tournaments**: http://localhost:8000/admin/tournaments
- **Create Tournament**: http://localhost:8000/admin/tournaments/create
- **Edit Tournament**: http://localhost:8000/admin/tournaments/{id}/edit

---

## File Locations

### Entities (Database Models)

- `src/Entity/Game.php` - Match/Game entity
- `src/Entity/Team.php` - Team entity
- `src/Entity/Player.php` - Player entity
- `src/Entity/Tournament.php` - Tournament entity

### Controllers

- `src/Controller/HomeController.php` - Home page
- `src/Controller/MatchController.php` - Front office matches
- `src/Controller/MatchAdminController.php` - Back office matches
- `src/Controller/TeamController.php` - Front office teams
- `src/Controller/TeamAdminController.php` - Back office teams
- `src/Controller/PlayerAdminController.php` - Back office players
- `src/Controller/TournamentController.php` - Front office tournaments
- `src/Controller/TournamentAdminController.php` - Back office tournaments

### Forms

- `src/Form/GameType.php` - Game form with validation
- `src/Form/TeamType.php` - Team form with validation
- `src/Form/PlayerType.php` - Player form with validation
- `src/Form/TournamentType.php` - Tournament form with validation

### Repositories (Database Queries)

- `src/Repository/GameRepository.php` - Game queries
- `src/Repository/TeamRepository.php` - Team queries
- `src/Repository/PlayerRepository.php` - Player queries
- `src/Repository/TournamentRepository.php` - Tournament queries

### Templates

- `templates/base.html.twig` - Base template with navigation
- `templates/home.html.twig` - Home page
- `templates/match/index.html.twig` - Matches list
- `templates/match/show.html.twig` - Match details
- `templates/match/upcoming.html.twig` - Upcoming matches
- `templates/team/index.html.twig` - Teams list
- `templates/team/show.html.twig` - Team details
- `templates/tournament/index.html.twig` - Tournaments list
- `templates/tournament/show.html.twig` - Tournament details
- `templates/tournament/upcoming.html.twig` - Upcoming tournaments
- `templates/admin/match/` - Match admin templates
- `templates/admin/team/` - Team admin templates
- `templates/admin/player/` - Player admin templates
- `templates/admin/tournament/` - Tournament admin templates

---

## Testing Search & Filter

### Search Examples

1. Go to `/matches` and search for a team name
2. Go to `/teams` and search for a team name
3. Go to `/admin/players` and search for a player nickname
4. Go to `/tournaments` and search for a tournament name

### Filter Examples

1. Go to `/matches` and filter by status (pending, ongoing, finished)
2. Go to `/tournaments` and filter by status
3. Admin sections support similar filtering

### Sorting Examples

1. Admin tables show created date and can be sorted
2. Matches are ordered by date by default
3. Teams are ordered by name by default

---

## Form Validation Testing

### Create a New Team (Test Validation)

1. Go to `/admin/teams/create`
2. Try leaving "Team Name" empty → Error: "Team name is required"
3. Try entering a name with 1 character → Error: "Team name must be at least 2 characters"
4. Enter valid data (2+ chars) → Success
5. See flash message "Team created successfully!"

### Create a New Match (Test Validation)

1. Go to `/admin/matches/create`
2. Try leaving "Team 1" empty → Error: "Team 1 is required"
3. Try entering negative scores → Error: "Score must be positive"
4. Try invalid status → Error: "Invalid status"
5. Fill all fields correctly → Success

### Create a Player (Test Validation)

1. Go to `/admin/players/create`
2. Test required fields (Nickname, First Name, Last Name)
3. Test that birth date must be in the past
4. Test that team selection is required

---

## Troubleshooting

### Database Issues

```bash
# Reset database (CAUTION: Deletes all data)
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Check database status
php bin/console doctrine:schema:validate
```

### Cache Issues

```bash
# Clear Symfony cache
php bin/console cache:clear --no-warmup

# Clear all cache
php bin/console cache:clear
```

### Missing Database

```bash
# Create the database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate
```

### Port Already in Use

```bash
# Use different port
symfony serve --port=8001
# or
php -S localhost:8001 -t public/
```

---

## Key Features Overview

| Feature               | Location                | Status      |
| --------------------- | ----------------------- | ----------- |
| Home Page             | `/`                     | ✅ Complete |
| Match Management      | `/admin/matches`        | ✅ Complete |
| Team Management       | `/admin/teams`          | ✅ Complete |
| Player Management     | `/admin/players`        | ✅ Complete |
| Tournament Management | `/admin/tournaments`    | ✅ Complete |
| Search Functionality  | All list pages          | ✅ Complete |
| Filtering             | Match, Tournament pages | ✅ Complete |
| Sorting               | Admin pages             | ✅ Complete |
| Form Validation       | All forms               | ✅ Complete |
| Entity Relationships  | All entities            | ✅ Complete |
| Responsive Design     | All pages               | ✅ Complete |
| CSRF Protection       | All forms               | ✅ Complete |

---

## Performance Tips

1. **Limit Data Loading**: Repository methods already use efficient queries
2. **Use Pagination**: Can be added for large datasets
3. **Cache Results**: Symfony caching can be configured
4. **Database Indexing**: Add indexes on frequently searched fields

---

## Security Notes

- All forms are CSRF protected
- Entity binding prevents direct manipulation
- Input validation is server-side
- Sensitive operations require confirmations
- No debug information in production mode

---

## Next Steps After Running

1. **Test Navigation**: Click through all menu items
2. **Create Data**: Add teams, players, tournaments, and matches
3. **Test Search**: Use search on list pages
4. **Test Validation**: Try invalid data on forms
5. **View Details**: Click on entities to see full details
6. **Test Delete**: Use delete buttons with confirmation

---

**Last Updated**: February 7, 2026
**Project**: Dev Esports PIDEV
**Framework**: Symfony 7 + Bootstrap 5
