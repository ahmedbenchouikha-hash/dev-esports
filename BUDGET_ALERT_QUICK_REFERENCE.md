# Budget Alert System - Quick Reference

## What Was Implemented

A complete email alert system that notifies team managers and admins when team budgets reach specific utilization thresholds.

## How It Works

### For Team Managers 👨‍💼

**Receive alerts when:**
- Budget reaches **75%** - Get warned to start planning reductions
- Budget reaches **90%** - Get alerted budget is nearly gone
- Budget **exceeds 100%** - Get critical notice of overspending

**What they can do:**
- Reduce or pause spending
- Plan future expenses carefully
- Request budget increase if needed
- Contact admin for assistance

### For Admins 👨‍💻

**Receive alerts when:**
- Any team reaches **90%** of budget - Monitor spending
- Any team **exceeds** budget - Take action required
  - Validate/reject pending expenses
  - Adjust team budget if needed
  - Contact team manager

## Alert Details

| Threshold | Manager | Admin | Type | Action |
|-----------|---------|-------|------|--------|
| 75% | ✅ Email | ❌ None | Attention | Plan reductions |
| 90% | ✅ Email | ✅ Email | High Alert | Monitor closely |
| 100%+ | ✅ Email | ✅ Email | Critical | Take immediate action |

## Email Examples

### What Manager Receives at 75%
```
Subject: 🟡 Attention: Budget à 75% pour l'équipe [TeamName]

"Your team has reached 75% of its allocated budget.
Recommended actions:
- Examine your current expenses
- Plan future expenses with caution
- Reduce optional spending if possible"
```

### What Manager Receives at 90%
```
Subject: 🔴 Alerte: Budget à 90% pour l'équipe [TeamName]

"Your team has reached 90% of its budget.
Situation:
- Utilization: 90%
- Budget remaining: [Amount]

Actions:
- Reduce non-essential spending
- Plan spending carefully
- Evaluate if budget increase is needed"
```

### What Manager Receives at 100%+
```
Subject: ⚠️ CRITIQUE: Budget dépassé pour l'équipe [TeamName]

"CRITICAL - Your team has exceeded its allocated budget!
Situation:
- Utilization: [Percentage]%
- Amount overrun: [Amount]

Actions:
- Reduce spending immediately
- Plan budget review
- Contact administrator for assistance"
```

### What Admin Receives at 90%
```
Subject: 🟠 L'équipe [TeamName] a atteint 90% de son budget

"Team [TeamName] has reached 90% of budget.
Details:
- Team: [TeamName]
- Utilization: 90%
- Budget remaining: [Amount]

Actions:
- Monitor future expenses
- Check pending expense validations
- Discuss with team manager if needed"
```

### What Admin Receives at 100%+
```
Subject: 🔴 CRITIQUE: Équipe [TeamName] a dépassé son budget

"CRITICAL - Team has EXCEEDED budget allocation!
Details:
- Team: [TeamName]
- Utilization: [Percentage]%
- Amount overrun: [Amount]

Actions:
- Verify team expenses
- Contact team manager
- Adjust budget if necessary
- Validate/reject pending expenses"
```

## How Alerts Trigger

1. **Manager/Admin validates an expense** in the system
2. System automatically checks team's budget
3. If threshold is reached:
   - Creates alert record in database
   - Sends email(s) to appropriate recipient(s)
   - Records when each notification was sent

## Important Details

### Alert Timing

- **First alert:** Sent when threshold is reached
- **Duplicate prevention:** Same alert type won't send more than once per hour per team
- **Independent tracking:** Manager and admin notifications tracked separately

### Who Gets Notified

**Managers (75% + 90% + 100%):**
- Players marked with `ROLE_MANAGER` on the team
- Identified by their role in the system

**Admins (90% + 100% only):**
- Currently: `melkimalek888@gmail.com`
- Future: Will support multiple admin recipients

## Database

All alerts are recorded in the `budget_alert` table:
- When sent
- What type
- Budget percentage at time
- To whom notifications were sent
- When manager/admin notifications were delivered

## Configuration

### To Change Admin Email

**File:** `src/Service/BudgetAlertService.php`  
**Line:** ~182  

```php
$adminEmail = 'melkimalek888@gmail.com'; // Change this
```

### To Change Alert Thresholds

**File:** `src/Service/BudgetAlertService.php`  
**Method:** `checkBudgetAndAlert()`  

Change these values:
```php
if ($budget->isDepassement()) {
    // 100%+ exceeded
} elseif ($percentageUsed >= 90) {
    // 90% reached
} elseif ($percentageUsed >= 75) {
    // 75% reached
}
```

## Testing

### To Test Alerts

1. Create a team budget: **1000€**
2. Create expense: **750€** → Validate
   - **75% reached** → Manager gets 🟡 email
3. Create expense: **200€** → Validate
   - **95% reached** → Managers + Admin get 🔴 emails
4. Create expense: **150€** → Validate
   - **105% exceeded** → Managers + Admin get ⚠️ emails

### To Check Sent Alerts

```sql
SELECT id, type, budget_percentage, sent_at, 
       manager_notification_sent_at, admin_notification_sent_at
FROM budget_alert
ORDER BY sent_at DESC
LIMIT 10;
```

## Troubleshooting

### Not Receiving Emails?

1. Check if expense was validated (status = 'validée')
2. Check if team has an active budget
3. Check if you have ROLE_MANAGER (for managers)
4. Verify email is configured in Symfony
5. Check application logs: `tail var/log/dev.log`

### Receiving Duplicate Emails?

1. This shouldn't happen (1-hour duplicate protection)
2. If it occurs, check `budget_alert` table
3. May be different alert types (75% vs 90%)

### Emails Have Wrong Information?

1. Clear Symfony cache: `php bin/console cache:clear`
2. Verify budget is properly calculated
3. Check expenses are marked 'validée'

## Future Features

Coming soon:
- 📊 Budget forecasting (predict exhaustion date)
- ⚙️ Customizable thresholds per team
- 📱 SMS and Slack notifications
- 🎯 Admin dashboard with quick actions
- 🌍 Multi-language support

---

**Last Updated:** February 24, 2026
