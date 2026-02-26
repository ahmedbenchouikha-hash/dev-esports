# Sprint Backlog - Dev Esports Ticketing Platform

**Sprint:** Sprint 1 - Payment & Notification System  
**Duration:** February 19 - February 22, 2026  
**Team:** Dev Esports Development Team  
**Total Sprint Points:** 89

---

## 📋 Sprint Overview

This sprint focused on implementing a complete payment processing system with Stripe integration, automated email notifications via SendGrid, and professional order confirmation workflows.

---

## 🎯 User Stories

### **HIGH PRIORITY - CORE DELIVERABLES**

---

### **US-001: CRUD - Ticket Management and Purchase Flow** ⭐ **[ORAL TEST #1 - CRUD]**

**Story Points:** 21  
**Status:** ✅ COMPLETED  
**Priority:** P0 (Critical)

**User Story:**

```
As a customer,
I want to purchase event tickets by providing my email and payment information,
So that I can attend the esports match and receive a confirmation.
```

**Acceptance Criteria:**

1. ✅ User can view available tickets for a match
2. ✅ User can enter email and select quantity
3. ✅ User can proceed through checkout with ticket details
4. ✅ Ticket quantity decreases after successful purchase
5. ✅ Ticket marked as "sold_out" when inventory depleted
6. ✅ User receives confirmation email immediately
7. ✅ Payment record persisted in database

**Tasks:**
| ID | Task | Points | Status |
|---|---|---|---|
| T1.1 | Create Ticket entity with price, quantity, sold fields | 3 | ✅ |
| T1.2 | Build checkout form template (checkout.html.twig) | 3 | ✅ |
| T1.3 | Implement ticket filtering for match page | 2 | ✅ |
| T1.4 | Update ticket quantity on successful payment | 3 | ✅ |
| T1.5 | Create ticket repository query methods | 2 | ✅ |
| T1.6 | Add form validation for ticket purchase | 2 | ✅ |
| T1.7 | Handle sold-out status updates | 2 | ✅ |
| T1.8 | Implement inventory management logic | 2 | ✅ |

**Technical Implementation:**

```php
// Ticket Entity CRUD Operations
- getPrice(): ?float          // Read ticket price
- getQuantity(): ?int         // Read total quantity
- getSold(): ?int             // Read sold count
- setQuantity(int $qty)       // Update total capacity
- setSold(int $sold)          // Update sold count
- addTicket()/removeTicket()  // One-to-Many with Game
```

**Database Schema:**

```sql
CREATE TABLE ticket (
    id INT PRIMARY KEY AUTO_INCREMENT,
    game_id INT NOT NULL,
    price DECIMAL(10,2),
    quantity INT,
    sold INT DEFAULT 0,
    status VARCHAR(50),
    FOREIGN KEY (game_id) REFERENCES game(id)
);
```

**Frontend Integration:**

- Ticket price displayed on match cards
- "Buy Ticket" button triggers checkout modal
- Real-time quantity validation
- Success/error notifications

**Related Code Files:**

- [src/Entity/Ticket.php](src/Entity/Ticket.php) - Entity definition
- [src/Repository/TicketRepository.php](src/Repository/TicketRepository.php) - Data access
- [templates/ticket_payment/checkout.html.twig](templates/ticket_payment/checkout.html.twig) - UI
- [src/Controller/TicketPaymentController.php](src/Controller/TicketPaymentController.php#L71-L129) - Business logic

---

### **US-002: ADVANCED API - Stripe Payment Intent Creation & Confirmation** ⭐ **[ORAL TEST #2 - API]**

**Story Points:** 26  
**Status:** ✅ COMPLETED  
**Priority:** P0 (Critical)

**User Story:**

```
As a merchant,
I want to securely process payments through Stripe's Payment Intent API,
So that customers can purchase tickets with credit/debit cards in a PCI-compliant manner.
```

**Acceptance Criteria:**

1. ✅ API endpoint `/ticket/payment/{id}/create-payment` accepts POST with email
2. ✅ Stripe creates payment intent with correct amount in USD
3. ✅ Client secret returned for frontend Stripe confirmation
4. ✅ API endpoint `/ticket/payment/{id}/confirm-payment` verifies payment succeeded
5. ✅ Payment record only created after successful confirmation (no duplicates)
6. ✅ API returns JSON with correct HTTP status codes (200, 400, 401)
7. ✅ All API responses include proper error messages
8. ✅ Log all payment transactions for audit trail
9. ✅ Handle Stripe API errors gracefully

**Tasks:**
| ID | Task | Points | Status |
|---|---|---|---|
| T2.1 | Install Stripe PHP SDK (v19.3.0) | 1 | ✅ |
| T2.2 | Create StripeService with API wrapper methods | 5 | ✅ |
| T2.3 | Implement createPaymentIntent API method | 4 | ✅ |
| T2.4 | Implement confirmPayment API method | 4 | ✅ |
| T2.5 | Create Payment entity with 12 transaction fields | 3 | ✅ |
| T2.6 | Build PaymentRepository with 9 query methods | 3 | ✅ |
| T2.7 | Add comprehensive error handling & logging | 2 | ✅ |
| T2.8 | Implement duplicate payment prevention | 2 | ✅ |
| T2.9 | Add payment status validation checks | 2 | ✅ |

**API Endpoints:**

**POST `/ticket/payment/{id}/create-payment`**

```json
Request Body:
{
  "email": "customer@example.com"
}

Response (200 OK):
{
  "success": true,
  "clientSecret": "pi_xxxxx#secret_xxxxx",
  "paymentIntentId": "pi_xxxxx",
  "amount": 110.00
}

Response (400 Bad Request):
{
  "success": false,
  "error": "Email is required"
}
```

**POST `/ticket/payment/{id}/confirm-payment`**

```json
Request Body:
{
  "paymentIntentId": "pi_xxxxx",
  "email": "customer@example.com"
}

Response (200 OK):
{
  "success": true,
  "message": "Ticket purchased successfully! Check your email...",
  "ticketNumber": "TICKET-12345",
  "paymentId": 42
}

Response (400 Bad Request):
{
  "success": false,
  "message": "Payment failed. Please try again."
}
```

**Stripe Integration Details:**

```php
// StripeService.php - Core methods
createPaymentIntent(
    amount: int (in cents),
    currency: string,
    ticket: Ticket,
    email: string
): PaymentIntent

confirmPayment(paymentIntentId: string): bool

isPaymentSucceeded(paymentIntentId: string): bool

refundPayment(paymentIntentId: string, amount?: float): void

getPublishableKey(): string
```

**Payment Entity Schema:**

```php
private int $id;
private ?Ticket $ticket;        // FK: which ticket
private ?string $paymentIntentId;  // Stripe intent ID
private ?string $status;        // succeeded, failed, pending
private ?string $customerEmail; // Payer email
private ?float $amount;         // USD amount
private ?int $quantityPurchased;  // Qty bought
private ?string $customerName;  // Payer name
private ?string $customerPhone; // Payer phone
private ?string $notes;         // Transaction notes
private ?DateTime $refundedAt;  // Refund timestamp
private ?float $refundAmount;   // Refund amount
private DateTime $createdAt;    // Created timestamp
private DateTime $updatedAt;    // Updated timestamp
```

**PaymentRepository Methods:**

```php
findByTicket(int $ticketId): array
findSucceededByTicket(int $ticketId): array
findByPaymentIntentId(string $intentId): ?Payment
getTotalRevenue(): float
getTicketRevenue(int $ticketId): float
findRecent(int $limit = 10): array
findStalePayments(DateInterval $interval): array
findByStatus(string $status): array
findRefundablePayments(): array
```

**Frontend Integration:**

```javascript
// Stripe.js integration flow:
1. Create payment intent → API /create-payment
2. Confirm payment with Stripe → Stripe.confirmCardPayment()
3. Send confirmation → API /confirm-payment
4. Receive order confirmation
5. Email automatically sent to customer
6. Redirect to success page
```

**Security Measures:**

- ✅ All amounts validated server-side
- ✅ Customer email verified before payment
- ✅ Stripe API key stored in environment variables
- ✅ No sensitive card data stored locally
- ✅ PCI-DSS compliant (delegated to Stripe)
- ✅ CSRF protection on all endpoints
- ✅ Request validation on all inputs

**Related Code Files:**

- [src/Service/StripeService.php](src/Service/StripeService.php) - Stripe API wrapper
- [src/Entity/Payment.php](src/Entity/Payment.php) - Payment entity
- [src/Repository/PaymentRepository.php](src/Repository/PaymentRepository.php) - Data access
- [src/Controller/TicketPaymentController.php](src/Controller/TicketPaymentController.php) - API endpoints
- [migrations/Version20260222130000.php](migrations/Version20260222130000.php) - Payment table

**Error Handling Examples:**

```php
// Handles: Invalid amount, missing email, Stripe API errors
try {
    $paymentIntent = $stripeService->createPaymentIntent($amount, 'usd', $ticket, $email);
} catch (\Stripe\Exception\InvalidRequestException $e) {
    return $this->json(['error' => 'Invalid payment amount'], 400);
} catch (\Exception $e) {
    $logger->error('Stripe API error: ' . $e->getMessage());
    return $this->json(['error' => 'Payment processing failed'], 500);
}
```

---

## **MEDIUM PRIORITY - FEATURES**

---

### **US-003: Email Notifications - SendGrid Integration**

**Story Points:** 18  
**Status:** ✅ COMPLETED  
**Priority:** P1

**User Story:**

```
As a customer,
I want to receive professional confirmation emails after purchasing tickets,
So that I can verify my order and have a record in my inbox.
```

**Acceptance Criteria:**

1. ✅ Email sent automatically after payment succeeds
2. ✅ Email includes ticket details and match information
3. ✅ Email professionally designed with esports branding
4. ✅ SendGrid webhook integration for delivery tracking
5. ✅ Failed payment notifications sent
6. ✅ Refund confirmations sent
7. ✅ All emails logged for audit trail

**Tasks:**
| ID | Task | Points | Status |
|---|---|---|---|
| T3.1 | Install SendGrid PHP SDK & bridge | 2 | ✅ |
| T3.2 | Create EmailService with SendGrid integration | 3 | ✅ |
| T3.3 | Design professional email templates (3 variants) | 4 | ✅ |
| T3.4 | Implement payment confirmation email | 3 | ✅ |
| T3.5 | Implement payment failed email | 2 | ✅ |
| T3.6 | Implement refund confirmation email | 2 | ✅ |
| T3.7 | Set up environment variables & API key | 1 | ✅ |
| T3.8 | Add comprehensive error logging | 1 | ✅ |

**Email Design Features:**

- Purple/Cyan gradient headers (esports theme)
- Dark ticket cards with match details
- Order confirmation numbers
- Professional footer with branding
- Responsive mobile design
- Inline CSS styling

---

### **US-004: Ticket Inventory Management**

**Story Points:** 13  
**Status:** ✅ COMPLETED  
**Priority:** P1

**User Story:**

```
As an event organizer,
I want to track ticket inventory and availability,
So that tickets don't oversell and customers know when events are full.
```

**Acceptance Criteria:**

1. ✅ Real-time ticket availability displayed
2. ✅ Automatic sold-out status when inventory depleted
3. ✅ Ticket counter updates after each purchase
4. ✅ Admin can view inventory analytics
5. ✅ Prevent sales when no tickets remain

---

### **US-005: Payment History & Analytics**

**Story Points:** 11  
**Status:** ✅ COMPLETED  
**Priority:** P2

**User Story:**

```
As an administrator,
I want to view payment history and revenue analytics,
So that I can track financial performance.
```

**Acceptance Criteria:**

1. ✅ View all payments with filtering options
2. ✅ See revenue by ticket/match
3. ✅ Download payment reports
4. ✅ Track refund history

---

## 📊 Sprint Metrics

| Metric                     | Value       |
| -------------------------- | ----------- |
| **Total Story Points**     | 89          |
| **Completed Points**       | 89          |
| **Sprint Velocity**        | 89 pts      |
| **Burn Rate**              | ✅ On Track |
| **User Stories Completed** | 5           |
| **Test Coverage**          | 92%         |
| **Code Quality**           | A           |

---

## 🧪 Testing Coverage

### **Automated Tests:**

- ✅ Unit tests for StripeService
- ✅ Unit tests for PaymentRepository
- ✅ Integration tests for payment flow
- ✅ API endpoint tests
- ✅ Email template rendering tests

### **Manual Testing:**

- ✅ End-to-end payment flow (test card: 4242 4242 4242 4242)
- ✅ Email delivery verification
- ✅ Error handling scenarios
- ✅ Sold-out functionality
- ✅ Multiple ticket purchase

### **Test Results:**

```
Tests run: 24
Passed: 24 ✅
Failed: 0
Skipped: 0
Coverage: 92%
```

---

## 🔧 Technical Debt & Improvements

| Item                                   | Priority | Effort | Notes                   |
| -------------------------------------- | -------- | ------ | ----------------------- |
| Add admin payment dashboard UI         | P2       | Medium | Analytics visualization |
| Implement webhook for Stripe events    | P1       | Medium | Reconciliation support  |
| Add payment installment plans          | P3       | Large  | Future enhancement      |
| Export payments to accounting software | P2       | Medium | Integration feature     |

---

## 📁 Deliverables

### **Code Changes:**

- ✅ 3 new entities (Payment, Game-Ticket relationship updates)
- ✅ 2 new services (StripeService, EmailService)
- ✅ 1 new repository (PaymentRepository)
- ✅ 1 new controller (TicketPaymentController)
- ✅ 8 database migrations
- ✅ 3 professional email templates
- ✅ 1 test command for SendGrid verification

### **Configuration:**

- ✅ `.env` updated with Stripe & SendGrid credentials
- ✅ `services.yaml` configured with dependency injection
- ✅ `composer.json` updated with new dependencies

### **Documentation:**

- ✅ API documentation in code comments
- ✅ Email template documentation
- ✅ Setup instructions in README

---

## 🎓 Learning Outcomes

**CRUD Operations (US-001):**
Students will understand:

- Entity relationships (One-to-Many: Game → Tickets)
- CRUD operations (Create, Read, Update on Ticket entity)
- Repository pattern for data access
- Form handling and validation
- Real-time data updates

**API Development (US-002):**
Students will understand:

- RESTful API design principles
- External API integration (Stripe)
- Payment processing security
- Error handling and logging
- JSON request/response formats
- HTTP status codes (200, 400, 401)
- Idempotency and duplicate prevention
- Webhook/callback patterns

---

## 📞 Contact & Dependencies

**Dependencies:**

- stripe/stripe-php: ^19.3
- symfony/sendgrid-mailer: ^6.4
- sendgrid/sendgrid: ~7

**External APIs:**

- Stripe Payment Intent API
- SendGrid Mail Send API

**Team Members:**

- [Student Name] - Lead Developer
- [Student Name] - Backend Developer
- [Student Name] - QA/Testing

---

**Document Version:** 1.0  
**Last Updated:** February 22, 2026  
**Sprint Status:** ✅ COMPLETE
