<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Psr\Log\LoggerInterface;

class MailerService
{
    private PHPMailer $mailer;

    public function __construct(
        private readonly string $smtpHost,
        private readonly int $smtpPort,
        private readonly string $smtpUsername,
        private readonly string $smtpPassword,
        private readonly string $fromEmail,
        private readonly string $fromName,
        private readonly bool $smtpEncryption,
        private readonly LoggerInterface $logger
    ) {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configure SMTP settings
     */
    private function configureSMTP(): void
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->smtpHost;
            $this->mailer->Port = $this->smtpPort;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->smtpUsername;
            $this->mailer->Password = $this->smtpPassword;
            $this->mailer->SMTPSecure = $this->smtpEncryption ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_NONE;
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
        } catch (PHPMailerException $e) {
            $this->logger->error('PHPMailer configuration error: ' . $e->getMessage());
        }
    }

    /**
     * Send a password reset email
     */
    public function sendPasswordResetEmail(string $toEmail, string $toName, string $resetUrl): bool
    {
        try {
            // Clear previous recipients
            $this->mailer->clearAllRecipients();
            
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request';
            
            // Build HTML email body
            $htmlBody = $this->buildPasswordResetEmailBody($resetUrl, $toName);
            $this->mailer->Body = $htmlBody;
            
            // Plain text alternative
            $this->mailer->AltBody = "Click the link to reset your password: " . $resetUrl;
            
            $this->mailer->send();
            $this->logger->info("Password reset email sent to {$toEmail}");
            
            return true;
        } catch (PHPMailerException $e) {
            $this->logger->error("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build password reset email HTML body
     */
    private function buildPasswordResetEmailBody(string $resetUrl, string $userName): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
                .button { display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Password Reset Request</h1>
                </div>
                <div class="content">
                    <p>Hello <strong>{$userName}</strong>,</p>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <p style="text-align: center;">
                        <a href="{$resetUrl}" class="button">Reset Password</a>
                    </p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p style="word-break: break-all; background-color: #fff; padding: 10px; border: 1px solid #ddd;">
                        {$resetUrl}
                    </p>
                    <p><strong>This link will expire in 1 hour.</strong></p>
                    <p>If you did not request a password reset, please ignore this email.</p>
                </div>
                <div class="footer">
                    <p>&copy; Esports Dev Platform. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Send a generic email
     */
    public function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody, ?string $altBody = null): bool
    {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $altBody ?? strip_tags($htmlBody);
            
            $this->mailer->send();
            $this->logger->info("Email sent to {$toEmail} with subject: {$subject}");
            
            return true;
        } catch (PHPMailerException $e) {
            $this->logger->error("Failed to send email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email to multiple recipients
     */
    public function sendEmailToMultiple(array $recipients, string $subject, string $htmlBody, ?string $altBody = null): bool
    {
        try {
            $this->mailer->clearAllRecipients();
            
            foreach ($recipients as $email => $name) {
                $this->mailer->addAddress($email, $name);
            }
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $altBody ?? strip_tags($htmlBody);
            
            $this->mailer->send();
            $this->logger->info("Email sent to " . count($recipients) . " recipients");
            
            return true;
        } catch (PHPMailerException $e) {
            $this->logger->error("Failed to send bulk email: " . $e->getMessage());
            return false;
        }
    }
}
