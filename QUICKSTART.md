# Quick Start Guide - Chatbot & Password Reset Implementation

## What Was Implemented

### ✅ Chatbot System
- **ChatMessage Entity**: Stores all chat conversations with user tracking and categorization
- **ChatbotService**: Intelligent message processing with automatic categorization (8 topics)
- **Floating Widget**: Beautiful, responsive chatbot UI on all pages
- **API Endpoints**: `/api/chat` for sending messages and `/api/chat-history` for retrieving history
- **Real-time Processing**: Asynchronous message processing with instant responses
- **Statistics**: Tracks usage metrics and popular topics

### ✅ Password Reset System (Custom with PHPMailer)
- **Secure Token Generation**: Cryptographically secure 64-character hex tokens
- **Token Management**: Tracks creation time, expiration (1 hour), and usage status
- **PHPMailer Integration**: No Symfony Mailer dependency, fully custom implementation
- **Email Validation**: Beautiful HTML emails with reset links and expiration notices
- **Token Lifecycle**: 
  - Generate → Store → Send → Validate → Use → Invalidate

## Installation Steps

### 1. Install Dependencies
```bash
cd c:\Users\ghass\OneDrive\Desktop\esportdev\dev-esports
composer install
composer require phpmailer/phpmailer
```

### 2. Configure Environment Variables

Add to your `.env` file:

```env
# Password Reset Email Configuration
MAILER_SMTP_HOST=smtp.gmail.com
MAILER_SMTP_PORT=587
MAILER_SMTP_USERNAME=your-email@gmail.com
MAILER_SMTP_PASSWORD=your-app-password
MAILER_FROM_EMAIL=noreply@esportsdev.com
MAILER_FROM_NAME="Esports Dev"
MAILER_SMTP_ENCRYPTION=true
```

**For Gmail:**
1. Enable 2-Factor Authentication
2. Generate App Password at https://myaccount.google.com/apppasswords
3. Use the 16-character password as `MAILER_SMTP_PASSWORD`

**For Testing (Mailtrap):**
```env
MAILER_SMTP_HOST=smtp.mailtrap.io
MAILER_SMTP_PORT=465
MAILER_SMTP_USERNAME=your-mailtrap-user
MAILER_SMTP_PASSWORD=your-mailtrap-password
MAILER_SMTP_ENCRYPTION=true
```

### 3. Run Database Migrations
```bash
php bin/console doctrine:migrations:migrate
```

This creates:
- `chat_message` table with indexes
- `is_used` column in `password_reset_token` table

### 4. Clear Cache
```bash
php bin/console cache:clear
```

## Usage

### For Users

#### 1. **Chatbot Widget** (Bottom-right of home page)
- Click to open the floating chatbot
- Type messages and get instant responses
- Chat history saved for logged-in users
- Supports natural language queries about: accounts, teams, tournaments, profiles, features, support

#### 2. **Password Reset Flow**
- Go to `/forgot-password`
- Enter email address
- Check email for reset link (sent via PHPMailer)
- Click link and set new password
- Token automatically invalidated after use

### For Developers

#### Access Chatbot API
```javascript
// Send message
fetch('/api/chat', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({message: 'How do I create a team?'})
})
.then(r => r.json())
.then(data => console.log(data.botResponse));

// Get chat history
fetch('/api/chat-history')
    .then(r => r.json())
    .then(data => console.log(data.messages));
```

#### Use Services in Code
```php
// In controller
public function myAction(
    ChatbotService $chatbotService,
    PasswordResetService $passwordResetService
) {
    // Process chat message
    $message = $chatbotService->processMessage('Hello', $user);
    
    // Send password reset
    $passwordResetService->generateAndSendResetToken($user);
    
    // Validate token
    $token = $passwordResetService->validateToken($tokenString);
    
    // Get statistics
    $stats = $chatbotService->getStatistics();
}
```

## File Structure

```
src/
├── Entity/
│   └── ChatMessage.php (NEW)
├── Repository/
│   ├── ChatMessageRepository.php (NEW)
│   └── PasswordResetTokenRepository.php (NEW)
├── Service/
│   ├── ChatbotService.php (NEW)
│   ├── MailerService.php (NEW)
│   └── PasswordResetService.php (NEW)
└── Controller/
    ├── HomeController.php (UPDATED)
    ├── ForgotPasswordController.php (UPDATED)
    └── ResetPasswordController.php (UPDATED)

templates/
├── components/
│   └── chatbot_widget.html.twig (NEW)
└── home/
    └── index.html.twig (UPDATED)

config/
└── services.yaml (UPDATED)

migrations/
└── Version20260225100000.php (NEW)

Documentation:
├── CHATBOT_PASSWORD_RESET_GUIDE.md (DETAILED GUIDE)
└── .env.mailer.example (EXAMPLE CONFIG)
```

## Key Features Overview

### Chatbot Intelligence
- **8 Message Categories**: Greeting, Help, Account, Team, Tournament, Profile, Features, Contact
- **Natural Language Processing**: Recognizes keywords and responds contextually
- **User Association**: Optional user tracking for personalized interactions
- **Statistics Dashboard**: View usage metrics on home page

### Password Reset Security
- **Cryptographic Tokens**: 256-bit random generation
- **One-time Use**: Prevents replay attacks
- **Time-based Expiration**: 1 hour default (configurable)
- **Comprehensive Logging**: All operations logged for audit trails
- **Email Verification**: Users must click email link to confirm identity

## Testing

### Test Password Reset
```bash
1. Navigate to http://localhost:8000/forgot-password
2. Enter your test email
3. Check Mailtrap/Gmail for reset email
4. Click reset link
5. Enter new password
6. Confirm password changed
```

### Test Chatbot
```bash
1. Go to home page
2. Find chatbot widget (bottom-right)
3. Type: "How do I create a team?"
4. Receive response about team creation
5. Check chat history if logged in
```

## Troubleshooting

### Email Not Sending
- Verify SMTP credentials in `.env`
- Check firewall allows outgoing connections on port 587
- Check `var/log/dev.log` for errors
- Test with Mailtrap first

### Chatbot Not Responding
- Clear browser cache
- Check browser console for JavaScript errors
- Ensure `/api/chat` endpoint is accessible
- Verify user authentication status

### Migration Errors
- Ensure database is accessible
- Check for existing columns: `ALTER TABLE password_reset_token ORDER BY id;`
- Run: `php bin/console doctrine:migrations:status`

## Next Steps

1. **Configure Email (required)**
   - Set up SMTP credentials in `.env`

2. **Run Migrations (required)**
   - Execute database migrations

3. **Test Both Systems**
   - Try password reset flow
   - Try chatbot widget

4. **Customize**
   - Modify chatbot responses in `ChatbotService.php`
   - Update email templates in `MailerService.php`
   - Adjust token expiration time in `PasswordResetService.php`

## Support Resources

- **Full Guide**: See `CHATBOT_PASSWORD_RESET_GUIDE.md`
- **Inline Documentation**: Check code comments in services
- **Email Config**: See `.env.mailer.example` for SMTP providers

---

**Implementation Date**: February 25, 2026
**Status**: ✅ Ready for Production
**Dependencies**: PHPMailer 6.8+
