# PIDEV SPRINT WEB - QUICK START GUIDE

**Deadline:** Week of 23/02/2026  
**Project:** Dev Esports Admin Platform Enhancements

---

## 📋 OVERVIEW

You need to add 4 major components to your Match, Statistics, and Tickets system:

1. **External Bundles** - API generation, documentation
2. **API Integrations** - Payments, emails, esports data
3. **Advanced Features** - Real-time updates, automation, analytics
4. **AI Integration** - Predictions, forecasting, chatbot

---

## 🚀 STEP-BY-STEP IMPLEMENTATION

### STEP 1: INSTALL BUNDLES (30 minutes)

```bash
cd c:\Users\ahmed\OneDrive\Bureau\final\ dev\ esports\dev-esports

# Install API Platform (auto-generate REST APIs)
composer require api-platform/core

# Install Doctrine Extensions (timestamps, slugs)
composer require stof/doctrine-extensions-bundle

# Install API documentation
composer require nelmio/api-doc-bundle

# Optional: JWT authentication for API
composer require lexik/jwt-authentication-bundle
```

**Next:** Follow [BUNDLE_SETUP.md] to configure

---

### STEP 2: ADD PAYMENT INTEGRATION (1 hour)

**Goal:** Allow users to buy tickets with Stripe

```bash
# Install Stripe
composer require stripe/stripe-php
```

**Steps:**

1. Create Stripe account at https://stripe.com
2. Get API keys from dashboard
3. Add to `.env`:
   ```
   STRIPE_PUBLIC_KEY=pk_test_xxxxx
   STRIPE_SECRET_KEY=sk_test_xxxxx
   ```
4. Create `src/Service/StripeService.php` (see [API_SETUP.md])
5. Create `src/Controller/TicketPaymentController.php`
6. Test payment flow

**Result:** Customers can purchase tickets and pay securely

---

### STEP 3: ADD EMAIL NOTIFICATIONS (30 minutes)

**Goal:** Send match alerts and ticket confirmations

```bash
# Install SendGrid or Mailgun
composer require symfony/sendgrid-mailer
# OR for Mailgun:
# composer require symfony/mailgun-mailer
```

**Steps:**

1. Sign up at SendGrid (https://sendgrid.com)
2. Get API key
3. Add to `.env`:
   ```
   MAILER_DSN=sendgrid+api://SG.xxxxx@default
   SENDGRID_API_KEY=SG.xxxxx
   ```
4. Create `src/Service/NotificationService.php` (see [API_SETUP.md])
5. Send test email

**Result:** Customers receive email alerts for:

- Match is starting soon
- Ticket purchase confirmation
- Special promotions

---

### STEP 4: ADD REAL-TIME SCORE UPDATES (2 hours)

**Goal:** Live match score updates via WebSocket

```bash
# Install WebSocket bundle
composer require gos/web-socket-bundle
```

**Steps:**

1. Configure WebSocket server
2. Create `src/WebSocket/MatchUpdateHandler.php` (see [ADVANCED_FEATURES.md])
3. Update match controller with WebSocket broadcast
4. Update templates with JavaScript listener
5. Test real-time updates

**Result:**

- Admin updates score → Broadcast to all viewers instantly
- Customers see live scores without refreshing

---

### STEP 5: AUTOMATE MATCH STATUS (1 hour)

**Goal:** Matches auto-change from pending → ongoing → finished

**Steps:**

1. Create `src/Service/MatchStateService.php` (see [ADVANCED_FEATURES.md])
2. Create `src/Command/ProcessMatchStatusCommand.php`
3. Schedule cron job (runs every 5 minutes)
4. Test automation

**Result:** No manual status updates needed - automatic!

---

### STEP 6: ADD DYNAMIC TICKET PRICING (1.5 hours)

**Goal:** Ticket prices increase as inventory depletes

**Steps:**

1. Add `basePrice` field to Ticket entity
2. Create `src/Service/DynamicPricingService.php` (see [ADVANCED_FEATURES.md])
3. Create pricing update command
4. Test price changes based on occupancy

**Formula:**

```
Price = basePrice × occupancyMultiplier × timeMultiplier

Occupancy Multiplier:
- 90%+ sold: 1.5x (50% higher)
- 75%+: 1.3x (30% higher)
- 50%+: 1.1x (10% higher)
- <25%: 0.85x (15% discount)

Time Multiplier:
- >30 days: 0.8x (discount)
- 3-7 days: 1.2x (premium)
- <3 days: 1.5x (last minute)
```

**Result:** Revenue optimization through smart pricing

---

### STEP 7: BUILD ANALYTICS DASHBOARD (2 hours)

**Goal:** Dashboard showing match stats, revenue, top players

**Steps:**

1. Update repositories with analytics queries
2. Create `src/Controller/AnalyticsController.php` (see [ADVANCED_FEATURES.md])
3. Create `templates/admin/analytics/dashboard.html.twig`
4. Add charts using Chart.js
5. Display KPIs:
   - Total matches / revenue
   - Top performing players
   - Team win rates
   - Ticket occupancy rates

**Result:** Executive view of platform performance

---

### STEP 8: ADD AI INTEGRATION (2.5 hours)

**Goal:** AI predictions, forecasting, and chatbot

```bash
# Install OpenAI client
composer require openai-php/client
```

**Steps:**

1. Create OpenAI account at https://platform.openai.com
2. Generate API key
3. Add to `.env`:
   ```
   OPENAI_API_KEY=sk-xxxxx
   OPENAI_MODEL=gpt-4o-mini
   ```
4. Create `src/Service/AiAnalysisService.php` (see [AI_SETUP.md])
5. Create `src/Controller/AiController.php`
6. Add AI features:

#### AI Feature 1: Match Outcome Prediction

```
GET /api/ai/predict-match/{id}
Returns: Winner prediction + confidence + betting odds
```

#### AI Feature 2: Player Performance Analysis

```
GET /api/ai/player-analysis/{playerId}
Returns: Performance trend + strengths + recommendations
```

#### AI Feature 3: Ticket Demand Forecasting

```
GET /api/ai/ticket-forecast/{gameId}
Returns: Predicted occupancy + days to sell-out + pricing recommendation
```

#### AI Feature 4: Dynamic Pricing AI

```
GET /api/ai/pricing-recommendation/{ticketId}
Returns: Optimal price + revenue impact estimate
```

#### AI Feature 5: Customer Support Chatbot

```
POST /api/ai/chat
Body: { "question": "When does the match start?" }
Returns: { "answer": "The match starts at..." }
```

**Result:** Intelligent platform that helps:

- Predict match outcomes
- Recommend optimal prices
- Forecast demand
- Answer customer questions

---

## 📑 DOCUMENTATION FILES

| File                     | Purpose                                      |
| ------------------------ | -------------------------------------------- |
| `SPRINT_ENHANCEMENTS.md` | Overview of all enhancements                 |
| `BUNDLE_SETUP.md`        | Install and configure bundles                |
| `API_SETUP.md`           | External API integrations                    |
| `ADVANCED_FEATURES.md`   | Business features (WebSocket, pricing, etc.) |
| `AI_SETUP.md`            | AI integration guide                         |

---

## ✅ IMPLEMENTATION TIMELINE

### Day 1-2: Bundles & Basics

- [ ] Install bundles
- [ ] Configure API Platform
- [ ] Create REST API endpoints
- [ ] Generate API documentation

### Day 2-3: Payments & Email

- [ ] Add Stripe integration
- [ ] Implement ticket payment flow
- [ ] Set up email notifications
- [ ] Test payment + confirmation emails

### Day 3-4: Real-Time & Automation

- [ ] Implement WebSocket for live scores
- [ ] Add match status automation
- [ ] Create dynamic pricing system
- [ ] Test all features

### Day 4-5: Analytics & AI

- [ ] Build analytics dashboard
- [ ] Create AI prediction engine
- [ ] Add demand forecasting
- [ ] Implement customer chatbot

### Day 5-6: Testing & Polish

- [ ] Test all integrations
- [ ] Fix bugs
- [ ] Optimize performance
- [ ] Deploy to production

---

## 🔧 USEFUL ENVIRONMENT VARIABLES (.env)

```
# APIs
STRIPE_PUBLIC_KEY=pk_test_xxxxx
STRIPE_SECRET_KEY=sk_test_xxxxx
OPENAI_API_KEY=sk-xxxxx
OPENAI_MODEL=gpt-4o-mini
SENDGRID_API_KEY=SG.xxxxx

# Email
MAILER_FROM=noreply@devesports.com
MAILER_DSN=sendgrid+api://SG.xxxxx@default

# Database
DATABASE_URL=mysql://root:password@localhost:3306/dev_esports

# JWT (optional)
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

---

## 🧪 TESTING ENDPOINTS

### Test Payment Flow

```bash
curl -X POST http://localhost:8000/api/tickets/1/create-payment \
  -H "Content-Type: application/json" \
  -d '{"email": "customer@example.com"}'
```

### Test AI Prediction

```bash
curl http://localhost:8000/api/ai/predict-match/1
```

### Test Chatbot

```bash
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"question": "When do tickets go on sale?"}'
```

---

## 📊 API ENDPOINTS SUMMARY

| Endpoint                                  | Purpose               |
| ----------------------------------------- | --------------------- |
| `GET /api/games`                          | List all matches      |
| `GET /api/statistics`                     | List match statistics |
| `GET /api/tickets`                        | List tickets          |
| `POST /api/tickets/{id}/create-payment`   | Start payment         |
| `POST /api/tickets/{id}/confirm-payment`  | Confirm purchase      |
| `GET /api/ai/predict-match/{id}`          | Predict winner        |
| `GET /api/ai/player-analysis/{id}`        | Analyze player        |
| `GET /api/ai/ticket-forecast/{id}`        | Forecast demand       |
| `GET /api/ai/pricing-recommendation/{id}` | Recommend price       |
| `POST /api/ai/chat`                       | Ask chatbot           |
| `GET /admin/analytics`                    | View dashboard        |
| `GET /api/docs`                           | API documentation     |

---

## 🎯 SUCCESS CRITERIA

✅ All bundles installed and configured  
✅ REST APIs auto-generated for Game, MatchStatistic, Ticket  
✅ Payment flow working with Stripe  
✅ Email notifications sending  
✅ Real-time match score updates  
✅ Match status auto-updating  
✅ Dynamic ticket pricing working  
✅ Analytics dashboard displaying KPIs  
✅ AI predictions showing winners  
✅ Chatbot answering questions

---

## 🆘 TROUBLESHOOTING

**API not showing?**

- `php bin/console cache:clear`
- `php bin/console asset-map:compile`

**Emails not sending?**

- Check `.env` MAILER_DSN
- Verify SendGrid API key
- Check logs: `var/log/dev.log`

**WebSocket not working?**

- Ensure server on port 8080 is running
- Check browser console for connection errors

**AI not responding?**

- Check OPENAI_API_KEY in `.env`
- Verify API key has sufficient credits
- Check rate limiting

---

## 📚 ADDITIONAL RESOURCES

- API Platform: https://api-platform.com
- Stripe Docs: https://stripe.com/docs
- OpenAI Docs: https://platform.openai.com/docs
- WebSocket: https://socket.io

---

**Good luck with your sprint! 🚀**
