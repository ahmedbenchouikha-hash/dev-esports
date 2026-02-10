# Database Setup Guide

## Quick Start Database Setup

### Step 1: Create Database

```bash
php bin/console doctrine:database:create
```

Expected output:

```
Created database "dev_esports" for connection named "default"
```

---

### Step 2: Generate and Run Migrations

Symfony will automatically create migration files for your entities.

```bash
# Generate migration from entity changes
php bin/console make:migration

# Run the migration
php bin/console doctrine:migrations:migrate
```

Expected output:

```
> doctrine:migrations:migrate

 [notice] Executing migration version 20260207...
 [ok] Successfully executed migration
```

---

## Database Structure

### Tables Created:

#### 1. `game` Table

```sql
CREATE TABLE game (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team1_id INT NOT NULL,
  team2_id INT NOT NULL,
  tournament_id INT NOT NULL,
  score1 INT NOT NULL,
  score2 INT NOT NULL,
  matchdate DATETIME NOT NULL,
  status VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (team1_id) REFERENCES team(id),
  FOREIGN KEY (team2_id) REFERENCES team(id),
  FOREIGN KEY (tournament_id) REFERENCES tournament(id)
)
```

#### 2. `team` Table

```sql
CREATE TABLE team (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  country VARCHAR(255),
  description LONGTEXT,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
)
```

#### 3. `player` Table

```sql
CREATE TABLE player (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  nickname VARCHAR(255) NOT NULL UNIQUE,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  birth_date DATE,
  role VARCHAR(255),
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE CASCADE
)
```

#### 4. `tournament` Table

```sql
CREATE TABLE tournament (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  description LONGTEXT,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status VARCHAR(255) NOT NULL,
  location VARCHAR(255),
  prize_pool NUMERIC(10, 2),
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
)
```

---

## Entity Relationships Map

### Game

- Has Many-to-One relationship with **Team** (as team1)
- Has Many-to-One relationship with **Team** (as team2)
- Has Many-to-One relationship with **Tournament**

### Team

- Has One-to-Many relationship with **Player**
- Has One-to-Many relationship with **Game** (as team1)
- Has One-to-Many relationship with **Game** (as team2)

### Player

- Has Many-to-One relationship with **Team**

### Tournament

- Has One-to-Many relationship with **Game**

---

## Loading Sample Data (Optional)

Create test data with fixtures:

### Step 1: Generate Fixture File

```bash
php bin/console make:fixtures GameFixtures
```

### Step 2: Create Sample Data

Edit `src/DataFixtures/GameFixtures.php`:

```php
<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\Player;
use App\Entity\Tournament;
use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Teams
        $teamFnatic = new Team();
        $teamFnatic->setName('Fnatic');
        $teamFnatic->setCountry('EU');
        $teamFnatic->setDescription('One of the best esports organizations');
        $manager->persist($teamFnatic);

        $teamG2 = new Team();
        $teamG2->setName('G2 Esports');
        $teamG2->setCountry('EU');
        $teamG2->setDescription('Legendary esports team');
        $manager->persist($teamG2);

        // Create Players
        $player1 = new Player();
        $player1->setNickname('Rekkles');
        $player1->setFirstName('Martin');
        $player1->setLastName('Larsson');
        $player1->setRole('ADC');
        $player1->setTeam($teamFnatic);
        $manager->persist($player1);

        $player2 = new Player();
        $player2->setNickname('Caps');
        $player2->setFirstName('Rasmus');
        $player2->setLastName('Borregaard');
        $player2->setRole('Mid');
        $player2->setTeam($teamG2);
        $manager->persist($player2);

        // Create Tournament
        $tournament = new Tournament();
        $tournament->setName('LEC Spring 2026');
        $tournament->setDescription('League of Legends European Championship');
        $tournament->setStartDate(new \DateTime('2026-02-09'));
        $tournament->setEndDate(new \DateTime('2026-04-30'));
        $tournament->setStatus('pending');
        $tournament->setLocation('Berlin, Germany');
        $tournament->setPrizePool(100000);
        $manager->persist($tournament);

        // Create Game
        $game = new Game();
        $game->setTeam1($teamFnatic);
        $game->setTeam2($teamG2);
        $game->setScore1(2);
        $game->setScore2(1);
        $game->setMatchdate(new \DateTime('2026-02-15 18:00'));
        $game->setStatus('finished');
        $game->setTournament($tournament);
        $manager->persist($game);

        $manager->flush();
    }
}
```

### Step 3: Load Fixtures

```bash
php bin/console doctrine:fixtures:load
```

---

## Database Verification

### Check Database Connection

```bash
php bin/console doctrine:database:create --if-not-exists
```

### Validate Schema

```bash
php bin/console doctrine:schema:validate
```

Expected output:

```
[OK] The database schema is in sync with the mapping files.
```

### View Database Tables

```bash
# For MySQL, connect to database:
mysql -u root -p dev_esports

# Show tables:
SHOW TABLES;

# Describe a table:
DESCRIBE game;
DESCRIBE team;
DESCRIBE player;
DESCRIBE tournament;
```

---

## Troubleshooting

### Error: Database already exists

```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
```

### Error: Migration pending

```bash
php bin/console doctrine:migrations:migrate
```

### Error: Schema mismatch

```bash
# Verify schema matches entities
php bin/console doctrine:schema:validate

# Generate a new migration if needed
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Error: Connection refused

Check `.env` file for database credentials:

```bash
# .env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/dev_esports"
```

---

## Database Backup

### Create Backup

```bash
mysqldump -u root -p dev_esports > backup.sql
```

### Restore Backup

```bash
mysql -u root -p dev_esports < backup.sql
```

---

## Development vs Production

### Development Database

```bash
# .env
DATABASE_URL="mysql://root:password@127.0.0.1:3306/dev_esports_dev"
```

### Production Database

```bash
# .env.local (never commit this)
DATABASE_URL="mysql://user:pass@db.host.com:3306/dev_esports_prod"
```

---

## Performance Optimization

### Add Database Indexes

```sql
-- Speed up searches
CREATE INDEX idx_team_name ON team(name);
CREATE INDEX idx_player_nickname ON player(nickname);
CREATE INDEX idx_game_status ON game(status);
CREATE INDEX idx_tournament_status ON tournament(status);

-- Foreign key indexes
CREATE INDEX idx_game_team1 ON game(team1_id);
CREATE INDEX idx_game_team2 ON game(team2_id);
CREATE INDEX idx_game_tournament ON game(tournament_id);
CREATE INDEX idx_player_team ON player(team_id);
```

---

## Database Queries Reference

### Common Queries

#### Find all matches for a team

```sql
SELECT g.* FROM game g
WHERE g.team1_id = 1 OR g.team2_id = 1
ORDER BY g.matchdate DESC;
```

#### Find players on a team

```sql
SELECT p.* FROM player p
WHERE p.team_id = 1
ORDER BY p.nickname;
```

#### Find matches in a tournament

```sql
SELECT g.* FROM game g
WHERE g.tournament_id = 1
ORDER BY g.matchdate;
```

#### Search teams

```sql
SELECT t.* FROM team t
WHERE t.name LIKE '%fnatic%'
ORDER BY t.name;
```

---

## Using Repository Methods

Your repositories already have these optimized query methods:

```php
// GameRepository
$gameRepository->findByStatus('finished');
$gameRepository->findByTournament($tournament);
$gameRepository->findBySearchTerm('Fnatic');
$gameRepository->findUpcoming();
$gameRepository->findAllOrdered('matchdate');

// TeamRepository
$teamRepository->findBySearchTerm('Fnatic');
$teamRepository->findAllOrdered('name');

// PlayerRepository
$playerRepository->findBySearchTerm('Rekkles');
$playerRepository->findByTeam($team);
$playerRepository->findAllOrdered('nickname');

// TournamentRepository
$tournamentRepository->findBySearchTerm('LEC');
$tournamentRepository->findByStatus('pending');
$tournamentRepository->findUpcoming();
$tournamentRepository->findAllOrdered('startDate');
```

---

## Database Commands Cheat Sheet

```bash
# Create database
php bin/console doctrine:database:create

# Drop database (CAUTION!)
php bin/console doctrine:database:drop --force

# Generate migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Validate schema
php bin/console doctrine:schema:validate

# Check database info
php bin/console doctrine:database:info

# Clear cache if needed
php bin/console cache:clear

# Dump SQL schema
php bin/console doctrine:schema:create --dump-sql

# Load fixtures
php bin/console doctrine:fixtures:load
```

---

**Database Status**: Ready for Production
**Entities**: 4 (Game, Team, Player, Tournament)
**Relationships**: 5 (Multiple Foreign Keys)
**Validation**: Server-side on all fields
**Generated**: February 7, 2026
