# RankUp – E-Sports Tournament Management Platform

## Overview

This project was developed as part of the **PIDEV – 3rd Year Engineering Program** at **Esprit School of Engineering** (Academic Year 2025–2026).

It consists of a full-stack web application for managing competitive E-Sports tournaments — allowing teams to register, compete in matches, track statistics, manage budgets, and handle rewards. The platform features both a public-facing Front Office for players and spectators and a Back Office for administrators and managers.

## Features

- **Team Management** – Create, edit, and manage E-Sports teams with full roster tracking
- **Player Registration & Profiles** – Player accounts with statistics, roles, and team membership
- **Tournament Organization** – Create and manage tournaments with brackets, scheduling, and prize pools
- **Match Tracking & Scoring** – Record match results, live scores, and match statistics
- **Ticket & Payment System** – Event ticketing with Stripe payment integration and QR code generation
- **Budget & Expense Management** – Track team and tournament budgets with configurable alert thresholds
- **Reward System** – Manage rewards and reward requests linked to tournament performance
- **Reclamation System** – User complaints with admin responses
- **Punition System** – Player discipline tracking with voice notifications
- **Manager Role** – Dedicated manager workflows with team invitations and approvals
- **AI-Powered Features** – Mistral AI login messages, AI reward analysis, AI ticket pricing
- **Real-Time Chat** – Team chat messaging system
- **Notifications** – In-app notification system for key events
- **Authentication & Security** – JWT authentication, CSRF protection, role-based access control
- **PDF Export** – Generate PDF reports via DomPDF
- **Email Notifications** – SendGrid and PHPMailer integration

## Tech Stack

### Frontend

- **Twig** – Symfony templating engine
- **Bootstrap 5** – Responsive UI with dark theme
- **Hotwired Stimulus** 3.2 – JavaScript controllers for interactivity
- **Hotwired Turbo** 7.3 – SPA-like page navigation
- **Symfony AssetMapper** – Native ES module importmaps (no Webpack)

### Backend

- **PHP** 8.2
- **Symfony** 6.4 – Full-stack framework (Security, Mailer, Validator, Form, Console)
- **Doctrine ORM** 3.6 – Database abstraction and entity management
- **MariaDB** 10.4 – Relational database
- **Stripe SDK** – Payment processing
- **Lexik JWT** – JSON Web Token authentication
- **KnpPaginator** – Pagination
- **VichUploader** – File upload handling
- **DomPDF** – PDF generation
- **Endroid QR Code** – QR code generation
- **Docker Compose** – Local development orchestration

## Architecture

The application follows a **3-tier MVC architecture**:

```
┌─────────────────────────────────────────────┐
│              UI Layer (Twig)                 │
│   Front Office  │  Back Office (Admin)      │
├─────────────────────────────────────────────┤
│           Controller Layer                   │
│  HomeController, MatchController,           │
│  TeamController, TournamentController,      │
│  AdminControllers, API Controllers ...      │
├─────────────────────────────────────────────┤
│     Service / Repository Layer               │
│  BudgetManager, AILoginMessageService,      │
│  GameRepository, TeamRepository ...         │
├─────────────────────────────────────────────┤
│           Entity Layer (Doctrine)            │
│  28 entities with validated relationships   │
├─────────────────────────────────────────────┤
│              Database (MariaDB)              │
└─────────────────────────────────────────────┘
```

**Key entities:** Game, Team, Player, Tournament, Ticket, Payment, Budget, BudgetAlert, Recompense, DemandeRecompense, Reclamation, Punition, User, UserProfile, and more (28 total).

**Entity relationships:**

- Tournament → Games (One-to-Many)
- Game → Team1 / Team2 (Many-to-One)
- Team → Players (One-to-Many)
- Player extends User (JOINED inheritance)
- Tournament → Recompenses → DemandeRecompenses (cascading)

## Contributors

| Name               | Role      |
| ------------------ | --------- |
| Ahmed Ben Chouikha | Developer |
| Melki malek        | Developer |
| Linda nebily       | Developer |
| mohamed khouja     | Developer |
| Ramzi ben hmida    | Developer |
| ghassen saidani    | Developer |

## Academic Context

Developed at **Esprit School of Engineering – Tunisia**

**PIDEV – 3A** | Academic Year 2025–2026

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Symfony CLI
- MariaDB / MySQL
- Docker (optional)

### Installation

```bash
# Clone the repository
git clone https://github.com/ahmedbenchouikha-hash/dev-esports.git
cd dev-esports

# Install PHP dependencies
composer install

# Configure environment
cp .env .env.local
# Edit .env.local with your database credentials

# Create the database and run migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Load seed data (optional)
php bin/console doctrine:fixtures:load

# Start the development server
symfony serve
```

Access the application at `http://localhost:8000`

## Acknowledgments

- **Esprit School of Engineering** – Academic supervision and project framework
- **Symfony** – Open-source PHP framework
- **Doctrine Project** – ORM and database abstraction
- **Bootstrap** – Frontend UI framework
- **Mistral AI** – AI-powered features integration
- Advanced statistics and analytics
- Mobile app API

## License

This project is part of PIDEV course work.

## Author

Ahmed - Dev Esports Team
