# 🔧 Tournament Join System - Integration & Setup Guide

## ✅ What Has Been Created

### Entities (Database Models)
- ✅ `src/Entity/TournamentRegistration.php` - Main registration entity
- ✅ `src/Entity/Tournament.php` - Updated with registration relationships
- ✅ `src/Entity/Team.php` - Updated with registration relationships

### Repository
- ✅ `src/Repository/TournamentRegistrationRepository.php` - Database queries

### Forms
- ✅ `src/Form/TournamentRegistrationType.php` - Registration form with validation

### Controllers
- ✅ `src/Controller/TournamentPlayerController.php` - Player routes (4 endpoints)
- ✅ `src/Controller/AdminTournamentRegistrationController.php` - Admin routes (4 endpoints)

### Templates
- ✅ `templates/tournament/available.html.twig` - Browse tournaments
- ✅ `templates/tournament/join_form.html.twig` - Join tournament form
- ✅ `templates/tournament/my_registrations.html.twig` - View registrations
- ✅ `templates/admin/tournament_registrations/list.html.twig` - Admin list
- ✅ `templates/admin/tournament_registrations/show.html.twig` - Admin detail view

### Database Migration
- ✅ `migrations/Version20260218150000.php` - Create tournament_registration table

### Documentation
- ✅ `TOURNAMENT_JOIN_SYSTEM.md` - Complete system design
- ✅ `TOURNAMENT_JOIN_IMPLEMENTATION.md` - What was implemented
- ✅ `TOURNAMENT_JOIN_QUICKSTART.md` - Quick reference guide
- ✅ `TOURNAMENT_JOIN_INTEGRATION_GUIDE.md` - This file

---

## 🚀 HOW TO INTEGRATE

### Step 1: Update Entity Imports
The Tournament and Team entities need to import the new TournamentRegistration class.

**Check that these imports exist in:**

**Tournament.php** - Should have:
```php
use App\Entity\TournamentRegistration;
```

**Team.php** - Should have:
```php
use App\Entity\TournamentRegistration;
```

✅ **Already done!** Both imports are in the updated files.

---

### Step 2: Run Database Migration

```bash
# Navigate to your project directory
cd /path/to/dev-esports-finaleone

# Create the tournament_registration table
php bin/console doctrine:migrations:migrate
```

**Expected output:**
```
                     Application Migrations                      

===============================================================================

 >> migrating Version20260218150000

    -> CREATE TABLE tournament_registration...

    -> Creating indexes...

    -> 46ms

 ++ migrated Version20260218150000
```

If you get an error about the migration already existing:
```bash
# You can manually check migrations
php bin/console doctrine:migrations:status

# Or sync with the database without running again
php bin/console doctrine:migrations:sync-metadata-storage
```

---

### Step 3: Clear Cache

```bash
# Clear Symfony cache
php bin/console cache:clear

# Optional: Warm up cache
php bin/console cache:warmup
```

---

### Step 4: Verify Entities

```bash
# Verify Doctrine mappings are correct
php bin/console doctrine:schema:validate
```

**Should show:**
```
[OK] The database schema is in sync with the current metadata.
```

If not in sync, you might need to run migrations again.

---

### Step 5: Test the Routes

#### Test Player Routes:

```bash
# List available tournaments
curl http://localhost:8000/tournaments/available

# View registration form (where {id} is a tournament ID)
curl http://localhost:8000/tournaments/1/join

# Check player's registrations
curl http://localhost:8000/tournaments/my-registrations
```

#### Test Admin Routes:

```bash
# List all registrations
curl http://localhost:8000/admin/tournament-registrations

# View registration detail (where {id} is a registration ID)
curl http://localhost:8000/admin/tournament-registrations/1
```

---

## 📝 Manual Setup (Alternative to Routes)

If routes don't work, ensure the routing configuration is correct.

**Check:** `config/routes.yaml`

Should have (or will auto-discover with attributes):
```yaml
# should auto-discover from #[Route] attributes in controllers
framework:
    router:
        utf8: true
```

✅ **Already handled!** Controllers use PHP attributes for routing.

---

## 🧪 Testing Workflow

### Scenario 1: Player Joins Tournament

**Step 1:** Log in as a player
```
URL: http://localhost:8000/login
```

**Step 2:** Browse tournaments
```
URL: http://localhost:8000/tournaments/available
```

**Step 3:** Fill form and submit
```
Click "Join Tournament" on any pending tournament
Fill motivation, experience level
Click "Submit Registration"
```

**Expected Result:**
- ✅ See success message
- ✅ Registration status shows "Pending"
- ✅ Form resets

---

### Scenario 2: Admin Reviews & Approves

**Step 1:** Log in as admin
```
URL: http://localhost:8000/login (as admin user)
```

**Step 2:** View registrations
```
URL: http://localhost:8000/admin/tournament-registrations
```

**Step 3:** Review a registration
```
Click "Review" button
```

**Step 4:** Approve or Reject
```
Option A: Add notes and click "Approve Registration"
Option B: Add reason and click "Reject Registration"
```

**Expected Result:**
- ✅ Status updates to "Approved" or "Rejected"
- ✅ Timestamp is recorded
- ✅ Notes are saved
- ✅ Flash message confirms action

---

### Scenario 3: Duplicate Prevention

**Step 1:** Join a tournament as player
```
Register team for tournament A
```

**Step 2:** Try to join same tournament again
```
Click "Join Tournament" on Tournament A
```

**Expected Result:**
- ✅ See message: "Your team is already registered"
- ✅ Redirected back to available tournaments
- ✅ No duplicate registration created ✅

---

## 🐛 Troubleshooting

### Issue: "Entity not found: TournamentRegistration"

**Solution:**
```bash
# Rebuild the autoloader
composer dump-autoload

# Clear cache
php bin/console cache:clear
```

---

### Issue: "Table 'tournament_registration' doesn't exist"

**Solution:**
```bash
# Run migrations
php bin/console doctrine:migrations:migrate

# If that doesn't work, force sync
php bin/console doctrine:schema:update --force
```

---

### Issue: "ROLE_PLAYER not found" or "ROLE_ADMIN not found"

**Solution:** Check `config/packages/security.yaml`

Users need these roles:
- Players: `ROLE_PLAYER`
- Admins: `ROLE_ADMIN`

---

### Issue: "The form's view data is not an instance of class"

**Solution:** The form expects a `TournamentRegistration` object:
```php
$registration = new TournamentRegistration();
$form = $this->createForm(TournamentRegistrationType::class, $registration);
```

✅ **Already handled in controllers!**

---

### Issue: Templates not found (404)

**Solution:** Check template paths:
- Player templates: `templates/tournament/`
- Admin templates: `templates/admin/tournament_registrations/`

All should be in place. If not found, verify they exist:
```bash
ls templates/tournament/
ls templates/admin/tournament_registrations/
```

---

## 📋 Pre-Launch Checklist

- [ ] All PHP files created (5 files)
- [ ] All templates created (5 files)
- [ ] Entity relationships updated
- [ ] Migration file created
- [ ] Database migration ran: `php bin/console doctrine:migrations:migrate`
- [ ] Cache cleared: `php bin/console cache:clear`
- [ ] Routes are accessible
- [ ] Player can view tournaments at `/tournaments/available`
- [ ] Player can join tournament (form works)
- [ ] Registration appears in admin panel
- [ ] Admin can approve registration
- [ ] Admin can reject registration
- [ ] Duplicate prevention works
- [ ] Form validation works
- [ ] Status badges display correctly
- [ ] Timestamps are recorded
- [ ] Flash messages appear

---

## 📊 Database Verification

```bash
# Verify table was created
php bin/console doctrine:query:sql "DESCRIBE tournament_registration;"
```

**Should show columns:**
- id
- tournament_id
- team_id
- player_id
- status
- motivation
- experience_level
- previous_achievements
- admin_notes
- created_at
- updated_at
- reviewed_at

---

## 🔄 Related Changes Summary

### Tournament Entity Changes:
```php
// Added relationship
#[ORM\OneToMany(mappedBy: 'tournament', targetEntity: TournamentRegistration::class, cascade: ['remove'])]
private Collection $registrations;

// Added methods:
- getRegistrations()
- addRegistration()
- removeRegistration()
- getApprovedRegistrations()
- getPendingRegistrations()
- getRejectedRegistrations()
```

### Team Entity Changes:
```php
// Added relationship
#[ORM\OneToMany(mappedBy: 'team', targetEntity: TournamentRegistration::class, cascade: ['remove'])]
private Collection $tournamentRegistrations;

// Added methods:
- getTournamentRegistrations()
- addTournamentRegistration()
- removeTournamentRegistration()
- isRegisteredInTournament()
- getApprovedTournamentRegistrations()
- getPendingTournamentRegistrations()
```

---

## 🎓 Code Quality

✅ **All files pass PHP syntax check**
✅ **Follows Symfony conventions**
✅ **Uses PHP attributes for routing**
✅ **Database relationships properly configured**
✅ **Form validation included**
✅ **Security checks implemented**
✅ **Responsive Bootstrap UI**
✅ **Error handling in place**

---

## 🚀 Next Steps After Integration

### Optional Enhancements:

1. **Email Notifications**
   ```php
   // Add to approval/rejection:
   $mailer->send($email);
   ```

2. **Dashboard Widget**
   ```html
   <!-- Show registration summary on dashboard -->
   <div>{{ app.user.team.pendingTournamentRegistrations|length }} pending registrations</div>
   ```

3. **Statistics Page**
   - Total registrations
   - Approval rate
   - Popular tournaments

4. **Export Registrations**
   - CSV export for admins
   - PDF reports

5. **Integration with Games**
   - Filter tournaments by game type
   - Verify team level matches tournament level

---

## 📞 Support Resources

### Documentation Files Created:
1. `TOURNAMENT_JOIN_SYSTEM.md` - Complete design specification
2. `TOURNAMENT_JOIN_IMPLEMENTATION.md` - What was built
3. `TOURNAMENT_JOIN_QUICKSTART.md` - User quick reference
4. `TOURNAMENT_JOIN_INTEGRATION_GUIDE.md` - This file

### Key Routes Reference:
```
PLAYER:
  GET  /tournaments/available
  GET  /tournaments/{id}/join
  POST /tournaments/{id}/join
  GET  /tournaments/my-registrations

ADMIN:
  GET  /admin/tournament-registrations
  GET  /admin/tournament-registrations/{id}
  POST /admin/tournament-registrations/{id}/approve
  POST /admin/tournament-registrations/{id}/reject
```

---

## ✨ Success Indicators

When everything is working correctly:

✅ Players see pending tournaments in card format
✅ Players can fill and submit join forms
✅ Admins see registrations in a table
✅ Admins can approve/reject with feedback
✅ Status updates appear in real-time
✅ Duplicate registrations are prevented
✅ Form validations work
✅ Flash messages appear
✅ Database records are created
✅ Timestamps are captured

---

## 🎉 You're Ready!

The tournament join system is complete and ready to use!

**Quick Start:**
1. Run migrations: `php bin/console doctrine:migrations:migrate`
2. Clear cache: `php bin/console cache:clear`
3. Log in as player: Visit `/tournaments/available`
4. Log in as admin: Visit `/admin/tournament-registrations`
5. Test the workflow!

Enjoy! 🚀
