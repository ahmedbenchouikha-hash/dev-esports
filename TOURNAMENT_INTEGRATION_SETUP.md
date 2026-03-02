# Tournament Module Integration Setup

## Step 1: Update Environment Variables

Add these lines to your `.env` file:

```env
# Google Gemini AI for Tournament Registration Review
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=models/gemini-pro

# RAWG API for Game Database
RAWG_API_KEY=your_rawg_api_key_here
RAWG_ALLOW_INSECURE_SSL=false
```

### How to get the keys:

1. **Gemini API Key:**
   - Go to https://ai.google.dev/
   - Create a project in Google Cloud Console
   - Enable the Generative AI API
   - Create an API key

2. **RAWG API Key:**
   - Go to https://rawg.io/apidocs
   - Register for a free API key
   - No authentication required for basic usage

## Step 2: Install Dependencies

Run the following command to install the new Gemini AI package and update dependencies:

```bash
composer install
```

This will install:

- `google/generative-ai` - For AI-powered tournament registration reviews

## Step 3: Create Database Tables

Run all migrations to create the new tournament-related tables:

```bash
php bin/console doctrine:migrations:migrate
```

This will create:

- `tournament` - Tournament records
- `tournament_registration` - Player registrations
- `game` - Match/game records
- `notification` - System notifications
- `password_reset_token` - Password reset tokens
- `admin_response` - Responses to player requests
- And other related tables

## Step 4: Update Config Files

✅ Already done:

- `config/services.yaml` - Added Gemini and RAWG parameters
- `config/bundles.php` - All bundles registered
- `composer.json` - Added google/generative-ai dependency

## Step 5: Copy Tournament Module Files

Copy these file categories from the tournament branch:

### New Controllers:

```
src/Controller/TournamentController.php
src/Controller/TournamentAdminController.php
src/Controller/Admin/TournamentRegistrationController.php
src/Controller/MatchAdminController.php
src/Controller/EquipeController.php
src/Controller/NotificationController.php
src/Controller/AdminResponseController.php
src/Controller/ForgotPasswordController.php
src/Controller/ResetPasswordController.php
src/Controller/UserController.php
src/Controller/AdminPlayerApprovalController.php
src/Controller/PlayerPendingApprovalController.php
src/Controller/MatchController.php
src/Controller/LandingPageController.php
src/Controller/RawgController.php
```

### New Entities:

```
src/Entity/Tournament.php
src/Entity/TournamentRegistration.php
src/Entity/Game.php
src/Entity/Notification.php
src/Entity/PasswordResetToken.php
src/Entity/AdminResponse.php
src/Entity/DemandeRecompense.php
src/Entity/Recompense.php
src/Entity/Punition.php
src/Entity/Reclamation.php
src/Entity/UserProfile.php
src/Enum/ReclamationStatus.php
src/Enum/ReclamationType.php
src/Enum/StatutPunition.php
```

### New Services:

```
src/Service/GeminiRegistrationReviewService.php  - AI Review
src/Service/NotificationService.php
src/Service/PdfGenerator.php
src/Security/LoginAuthenticator.php      - MERGE with existing
```

### New Repositories:

```
src/Repository/TournamentRepository.php
src/Repository/TournamentRegistrationRepository.php
src/Repository/GameRepository.php
src/Repository/NotificationRepository.php
src/Repository/PasswordResetTokenRepository.php
src/Repository/AdminResponseRepository.php
src/Repository/DemandeRecompenseRepository.php
src/Repository/RecompenseRepository.php
src/Repository/PunitionRepository.php
src/Repository/ReclamationRepository.php
src/Repository/UserProfileRepository.php
```

### New Forms:

```
src/Form/TournamentType.php
src/Form/TournamentRegistrationType.php
src/Form/GameType.php
src/Form/DemandeRecompenseType.php
src/Form/PunitionType.php
src/Form/ReclamationType.php
src/Form/RecompenseType.php
src/Form/AdminResponseType.php
```

### New Templates:

```
templates/tournament/                    - All tournament views
templates/admin/tournament/              - Admin tournament management
templates/admin/tournament_registrations/ - Registration management
templates/admin/dashboard.html.twig      - Main admin dashboard
templates/admin/_sidebar.html.twig       - Admin sidebar
templates/admin_response/                - Response management
templates/match/                         - Match views
templates/notification/                  - Notifications
templates/punition/                      - Punishments
templates/reclamation/                   - Complaints
templates/recompense/                    - Rewards
templates/security/email/               - Email templates
templates/rawg/                          - Game search
templates/landing.html.twig             - Landing page
```

### Migrations:

```
migrations/Version20260208132314.php
migrations/Version20260210120000.php
migrations/Version20260211182607.php
migrations/Version20260211200000.php
migrations/Version20260211210000.php
migrations/Version20260211220000.php
migrations/Version20260211232000.php
migrations/Version20260212000000.php
migrations/Version20260212010000.php
migrations/Version20260212020000.php
migrations/Version20260212030000.php
migrations/Version20260213000000.php
migrations/Version20260218134603.php
migrations/Version20260218150000.php
```

### CSS & Assets:

```
public/css/admin-tournaments.css          - Admin styling
assets/styles/app.css                     - MERGE with current (keep animations)
```

## Step 6: Update Existing Files (MERGE carefully)

### User Entity:

- Add tournament-related roles and properties
- Keep existing fields

### Team Entity:

- Add tournament relationships
- Keep your legend data and current structure

### Security Configuration:

- Add new roles for tournament management
- Keep existing RBAC

### Navigation (sidebar):

- Add tournament menu items
- Keep existing links

## Step 7: Clear Cache and Test

```bash
php bin/console cache:clear
php bin/console doctrine:migrations:migrate
php bin/console fixtures:load  # Optional: load test data
```

## Step 8: Test Tournament Features

1. Login to admin dashboard
2. Create a tournament
3. Register as a player
4. Check if AI review service works (check logs if errors)

## Important Notes

⚠️ **Backup before integration:**

```bash
git stash
git checkout -b backup-before-tournament-integration
```

✅ **Your current features preserved:**

- Player dashboard with QR codes and tickets
- Depense/Budget management
- Finance stats dashboard
- Teams management with legend sync
- Esports animations

✅ **New features added:**

- Tournament creation and management
- Player registration with AI review
- Match scheduling and tracking
- Notifications system
- Reward/Punishment system
- Complaint/Reclamation system
- Admin approval workflows
- Password reset functionality
- Game database integration (RAWG)

## Troubleshooting

### Gemini API Errors:

- Check API key is correct
- Verify API is enabled in Google Cloud Console
- Check quota limits

### Migration Errors:

- Ensure database backup exists
- Run `php bin/console doctrine:migrations:execute --cancel` to rollback

### Missing Services:

- Run `php bin/console debug:container` to verify services
- Check config/services.yaml for any syntax errors

## Next Steps

After integration:

1. Run full test suite
2. Update database seed with tournament data
3. Create admin user account
4. Setup email notifications (SMTP configuration)
5. Test all workflows end-to-end
