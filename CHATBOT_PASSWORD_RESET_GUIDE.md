# Chatbot & Password Reset System Implementation Guide

## Overview

This document provides comprehensive information about the newly integrated Chatbot System and Custom Forgot Password System using PHPMailer.

## Table of Contents

1. [Chatbot System](#chatbot-system)
2. [Password Reset System](#password-reset-system)
3. [Configuration](#configuration)
4. [Database Setup](#database-setup)
5. [API Endpoints](#api-endpoints)
6. [Integration Guide](#integration-guide)

---

## Chatbot System

### Features

- **Intelligent Message Categorization**: Automatically categorizes user messages into topics
- **Smart Responses**: Provides contextual responses based on message categories
- **Chat History**: Stores conversation history for users
- **Statistics**: Tracks chatbot usage metrics
- **Real-time Processing**: Processes messages asynchronously via API

### Supported Categories

- **Greeting**: Hello, Hi, Hey, Greetings, etc.
- **Help**: Help, Assist, Guide, Support requests
- **Account**: Password, Profile, Login, Email, Settings
- **Team**: Team creation, Joining, Members, Organization
- **Tournament**: Competition, League, Matches, Games
- **Profile**: Personal profile, Avatar, Bio, Personalization
- **Features**: Platform capabilities and functions
- **Contact**: Support contact information
- **Default**: General inquiries

### ChatMessage Entity

```php
// Stores chat conversations
- id: int
- user: User (nullable, allows anonymous chats)
- userMessage: string (max 500 chars)
- botResponse: text
- category: string (auto-categorized)
- createdAt: datetime_immutable
```

### ChatbotService

**Key Methods:**

```php
processMessage(string $userMessage, ?User $user = null): ChatMessage
- Processes user message and generates response
- Automatically categorizes message
- Stores conversation in database
- Returns ChatMessage entity with bot response

getChatHistory(?User $user, int $limit = 20): array
- Retrieves user's chat history
- Returns array of ChatMessage objects

getPopularTopics(int $limit = 5): array
- Returns most frequently discussed topics

getStatistics(): array
- Returns usage statistics:
  - totalMessages: Total chat messages processed
  - uniqueUsers: Number of unique users
  - messagesByCategory: Breakdown by category
```

### Integration in Home Page

The chatbot appears as a floating widget in the bottom-right corner of the home page. It includes:

- **Chat history display** with scrollable messages
- **Message input field** for user queries
- **Real-time responses** from the AI assistant
- **Responsive design** for mobile and desktop
- **Toggle button** to minimize/expand

To include the chatbot widget in any template:

```twig
{% include 'components/chatbot_widget.html.twig' %}
```

---

## Password Reset System

### Features

- **Secure Token Generation**: Uses `random_bytes()` for cryptographically secure tokens
- **Token Expiration**: Tokens expire after 1 hour (configurable)
- **One-time Use**: Tokens become invalid after first use
- **Automatic Cleanup**: Expired tokens are automatically cleared
- **PHPMailer Integration**: No dependency on Symfony Mailer
- **Email Validation**: Comprehensive token validation before password update

### PasswordResetToken Entity

```php
- id: int
- user: User (foreign key, cascade delete)
- token: string (128 chars, unique, hashed)
- createdAt: datetime_immutable
- expiresAt: datetime_immutable
- isUsed: boolean (default: false)
```

### PasswordResetService

**Key Methods:**

```php
generateAndSendResetToken(User $user): bool
- Generates secure token
- Invalidates existing tokens for user
- Sends reset email with token link
- Returns success status

validateToken(string $token): ?PasswordResetToken
- Validates token format and existence
- Checks if token is already used
- Checks if token has expired
- Returns PasswordResetToken if valid, null otherwise

invalidateToken(PasswordResetToken $tokenEntity): bool
- Marks token as used to prevent reuse
- Prevents token reuse attacks

cleanupExpiredTokens(): int
- Maintenance task to remove expired tokens
- Can be scheduled as a cron job
```

### MailerService

**Standalone PHPMailer Implementation**

```php
sendPasswordResetEmail(string $toEmail, string $toName, string $resetUrl): bool
- Sends formatted password reset email
- Includes reset link in button and plain text
- Includes expiration time notice
- Returns success status

sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody, ?string $altBody = null): bool
- Generic email sending method
- Supports custom HTML and plain text

sendEmailToMultiple(array $recipients, string $subject, string $htmlBody, ?string $altBody = null): bool
- Sends email to multiple recipients
- Useful for notifications and announcements
```

### Flow Diagram

```
1. User clicks "Forgot Password"
2. User enters email address
3. System checks if user exists
4. If exists:
   - Generate secure token
   - Store in database with expiration
   - Send email with reset link
5. User receives email with reset link
6. User clicks link and enters new password
7. System validates token:
   - Check if token exists
   - Check if token is expired
   - Check if token is already used
8. If valid:
   - Hash new password
   - Update user password
   - Mark token as used
   - Show success message
9. If invalid:
   - Show error message
   - Redirect to reset request
```

---

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# SMTP Configuration
MAILER_SMTP_HOST=smtp.gmail.com
MAILER_SMTP_PORT=587
MAILER_SMTP_USERNAME=your-email@gmail.com
MAILER_SMTP_PASSWORD=your-app-password
MAILER_FROM_EMAIL=noreply@esportsdev.com
MAILER_FROM_NAME="Esports Dev Platform"
MAILER_SMTP_ENCRYPTION=true
```

### SMTP Provider Examples

#### Gmail
```env
MAILER_SMTP_HOST=smtp.gmail.com
MAILER_SMTP_PORT=587
MAILER_SMTP_USERNAME=your-email@gmail.com
MAILER_SMTP_PASSWORD=your-app-specific-password
MAILER_SMTP_ENCRYPTION=true
```

#### Mailtrap (recommended for testing)
```env
MAILER_SMTP_HOST=smtp.mailtrap.io
MAILER_SMTP_PORT=465
MAILER_SMTP_USERNAME=your-mailtrap-username
MAILER_SMTP_PASSWORD=your-mailtrap-password
MAILER_SMTP_ENCRYPTION=true
```

#### SendGrid
```env
MAILER_SMTP_HOST=smtp.sendgrid.net
MAILER_SMTP_PORT=587
MAILER_SMTP_USERNAME=apikey
MAILER_SMTP_PASSWORD=SG.your-sendgrid-api-key
MAILER_SMTP_ENCRYPTION=true
```

### Services Configuration

Located in `config/services.yaml`:

```yaml
App\Service\MailerService:
    arguments:
        $smtpHost: '%mailer.smtp_host%'
        $smtpPort: '%mailer.smtp_port%'
        $smtpUsername: '%mailer.smtp_username%'
        $smtpPassword: '%mailer.smtp_password%'
        $fromEmail: '%mailer.from_email%'
        $fromName: '%mailer.from_name%'
        $smtpEncryption: '%mailer.smtp_encryption%'
```

---

## Database Setup

### Run Migrations

```bash
php bin/console doctrine:migrations:migrate
```

This creates/updates:
- `password_reset_token` table (adds `is_used` column)
- `chat_message` table with indexes

### Manual SQL (if needed)

```sql
-- Update password_reset_token table
ALTER TABLE password_reset_token ADD COLUMN is_used TINYINT(1) NOT NULL DEFAULT 0;

-- Create chat_message table
CREATE TABLE chat_message (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT DEFAULT NULL,
    user_message VARCHAR(500) NOT NULL,
    bot_response LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'general',
    PRIMARY KEY(id),
    INDEX IDX_FAB3FC16A76ED395 (user_id),
    INDEX idx_user_created_at (user_id, created_at),
    CONSTRAINT FK_FAB3FC16A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## API Endpoints

### Chatbot API

#### Send Message
**Endpoint:** `POST /api/chat`

**Authentication:** Required (CSRF token in form data or headers)

**Request Body:**
```json
{
    "message": "How can I create a team?"
}
```

**Response (Success):**
```json
{
    "success": true,
    "userMessage": "How can I create a team?",
    "botResponse": "Teams are the core...",
    "category": "team"
}
```

**Response (Error):**
```json
{
    "error": "Message cannot be empty"
}
```

#### Get Chat History
**Endpoint:** `GET /api/chat-history`

**Authentication:** Required (User must be logged in)

**Response:**
```json
{
    "success": true,
    "messages": [
        {
            "userMessage": "Hello",
            "botResponse": "Hi there! How can I help?",
            "category": "greeting",
            "createdAt": "2026-02-25 12:30:45"
        }
    ]
}
```

---

## Integration Guide

### 1. Composer Dependencies

Make sure PHPMailer is installed:

```bash
composer require phpmailer/phpmailer
```

### 2. Include Chatbot in Templates

Add to any template where you want the chatbot:

```twig
{# In base.html.twig or any page template #}
{% include 'components/chatbot_widget.html.twig' %}
```

### 3. Password Reset Flow

The system is already integrated:

1. User visits `/forgot-password`
2. Submits email form
3. Automatic email sent with reset link
4. User clicks link: `/reset-password/{token}`
5. New password form displayed
6. Password updated and token invalidated

### 4. Scheduled Tasks (Optional)

To periodically clean up expired tokens, add to your scheduler:

```php
// In a scheduled command
$this->passwordResetService->cleanupExpiredTokens();
```

Or create a console command:

```bash
php bin/console app:cleanup-expired-tokens
```

### 5. Testing PHPMailer

To test without sending real emails:

```php
// In development, use Mailtrap or similar fake SMTP service
MAILER_SMTP_HOST=smtp.mailtrap.io
MAILER_SMTP_PORT=465
```

---

## Usage Examples

### From Controller

```php
namespace App\Controller;

use App\Service\ChatbotService;
use App\Service\PasswordResetService;

class MyController extends AbstractController
{
    public function __construct(
        private readonly ChatbotService $chatbotService,
        private readonly PasswordResetService $passwordResetService
    ) {}

    public function handleChat(Request $request): Response
    {
        $user = $this->getUser();
        $message = $request->get('message');
        
        $chatMessage = $this->chatbotService->processMessage($message, $user);
        
        return new JsonResponse([
            'response' => $chatMessage->getBotResponse()
        ]);
    }

    public function forgotPassword(Request $request): Response
    {
        $email = $request->get('email');
        $user = $this->userRepository->findOneBy(['email' => $email]);
        
        if ($user) {
            $this->passwordResetService->generateAndSendResetToken($user);
        }
        
        return $this->redirectToRoute('app_forgot_password_check_email');
    }
}
```

### From Command

```php
namespace App\Command;

use App\Service\PasswordResetService;
use Symfony\Component\Console\Command\Command;

class CleanupExpiredTokensCommand extends Command
{
    protected static $defaultName = 'app:cleanup-expired-tokens';

    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->passwordResetService->cleanupExpiredTokens();
        $output->writeln("Cleaned up {$count} expired tokens.");
        
        return Command::SUCCESS;
    }
}
```

---

## Security Considerations

1. **Token Generation**: Uses cryptographically secure `random_bytes()`
2. **Token Storage**: Tokens are stored in database, not in user sessions
3. **Token Validation**: Always validate token format, expiration, and usage status
4. **HTTPS Required**: Always use HTTPS for password reset links in production
5. **Rate Limiting**: Consider implementing rate limiting on password reset endpoint
6. **Email Verification**: Verify email ownership through link clicks
7. **CSRF Protection**: Symfony CSRF tokens protect forms automatically
8. **Logging**: All operations are logged for audit purposes

---

## Troubleshooting

### Email Not Sending

1. Check SMTP credentials in `.env`
2. Verify firewall allows outgoing SMTP connections
3. Check logs in `var/log/dev.log`
4. Test with Mailtrap SMTP for development

### Token Validation Fails

1. Ensure token hasn't expired (1 hour default)
2. Check token hasn't been used before
3. Verify token exists in database
4. Check logs for detailed error messages

### Chatbot Not Responding

1. Verify `/api/chat` endpoint is accessible
2. Check browser console for JavaScript errors
3. Ensure user is authenticated for history
4. Verify ChatMessage entity is properly mapped

---

## Files Created/Modified

### New Files
- `src/Entity/ChatMessage.php` - Chat message entity
- `src/Repository/ChatMessageRepository.php` - Chat repository
- `src/Repository/PasswordResetTokenRepository.php` - Token repository
- `src/Service/MailerService.php` - PHPMailer wrapper service
- `src/Service/PasswordResetService.php` - Password reset logic service
- `src/Service/ChatbotService.php` - Chatbot logic service
- `templates/components/chatbot_widget.html.twig` - Chatbot UI widget
- `migrations/Version20260225100000.php` - Database migration
- `.env.mailer.example` - Environment configuration example

### Modified Files
- `composer.json` - Added PHPMailer dependency
- `config/services.yaml` - Added service configurations
- `src/Entity/PasswordResetToken.php` - Added `isUsed` field
- `src/Controller/ForgotPasswordController.php` - Refactored to use PasswordResetService
- `src/Controller/ResetPasswordController.php` - Refactored to use PasswordResetService
- `src/Controller/HomeController.php` - Integrated chatbot features and APIs

---

## License

This implementation is part of the Esports Dev Platform.

---

## Support

For issues or questions, refer to the inline code documentation or contact support.
