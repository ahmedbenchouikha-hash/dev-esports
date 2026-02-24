<?php

namespace App\Service;

use App\Entity\Payment;
use Psr\Log\LoggerInterface;
use SendGrid;
use SendGrid\Mail\Mail;

class EmailService
{
    private LoggerInterface $logger;
    private string $fromEmail;
    private string $apiKey;

    public function __construct(LoggerInterface $logger, string $fromEmail = 'noreply@devestports.com', string $apiKey = '')
    {
        $this->logger = $logger;
        $this->fromEmail = $fromEmail;
        $this->apiKey = $apiKey;
    }

    /**
     * Send generic email
     */
    public function send(string $to, string $subject, string $htmlContent): void
    {
        try {
            $email = new Mail();
            $email->setFrom($this->fromEmail, "Dev Esports");
            $email->setSubject($subject);
            $email->addTo($to);
            $email->addContent("text/html", $htmlContent);

            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                $this->logger->info('Email sent successfully', ['to' => $to, 'status' => $response->statusCode()]);
            } else {
                $this->logger->error('SendGrid returned non-success status', [
                    'status' => $response->statusCode(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email', ['error' => $e->getMessage(), 'to' => $to]);
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation(Payment $payment): void
    {
        try {
            $ticket = $payment->getTicket();
            $game = $ticket->getGame();
            $customerEmail = $payment->getCustomerEmail();

            $this->logger->info('Starting payment confirmation email', ['to' => $customerEmail, 'paymentId' => $payment->getId()]);

            $subject = sprintf('🎫 Ticket Purchase Confirmation - %s vs %s', 
                $game->getTeam1() ? $game->getTeam1()->getName() : 'Team 1',
                $game->getTeam2() ? $game->getTeam2()->getName() : 'Team 2'
            );

            $htmlContent = $this->renderPaymentConfirmationHtml($payment, $ticket, $game);
            $this->send($customerEmail, $subject, $htmlContent);
            $this->logger->info('Payment confirmation email sent', ['to' => $customerEmail]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send payment confirmation', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send payment failed email
     */
    public function sendPaymentFailed(string $to, string $ticketType, string $matchTitle): void
    {
        try {
            $subject = '❌ Payment Failed - ' . $matchTitle;
            $htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: bold; }
        .content { padding: 40px 30px; }
        .alert-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .alert-title { font-weight: bold; color: #991b1b; margin-bottom: 10px; }
        .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #b622d1 0%, #00d9ff 100%); color: white; text-decoration: none; border-radius: 6px; font-weight: bold; text-align: center; margin: 20px 0; }
        .footer { text-align: center; padding: 30px 20px; color: #666; font-size: 12px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Payment Failed</h1>
            <p>We couldn't process your payment</p>
        </div>
        <div class="content">
            <div class="alert-box">
                <div class="alert-title">⚠️ Your payment for $ticketType could not be processed.</div>
                <p>This could be due to:</p>
                <ul>
                    <li>Insufficient funds</li>
                    <li>Card expired or blocked</li>
                    <li>Incorrect card details</li>
                    <li>Your bank declined the transaction</li>
                </ul>
            </div>
            <p><strong>What should you do?</strong></p>
            <ul>
                <li>Check your card details and try again</li>
                <li>Use a different payment method</li>
                <li>Contact your bank if the issue persists</li>
            </ul>
            <a href="https://dev-esports.local/ticket/checkout" class="button">Try Again</a>
            <p style="color: #666; font-size: 14px;">If you continue to experience issues, please contact our support team.</p>
        </div>
        <div class="footer">
            <p><strong>Dev Esports</strong></p>
            <p>© 2026 Dev Esports. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
            $this->send($to, $subject, $htmlContent);
            $this->logger->info('Payment failed email sent', ['to' => $to]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send payment failed email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send refund confirmation email
     */
    public function sendRefundConfirmation(Payment $payment, float $refundAmount): void
    {
        try {
            $ticket = $payment->getTicket();
            $game = $ticket->getGame();
            $team1Name = $game->getTeam1() ? $game->getTeam1()->getName() : 'Team 1';
            $team2Name = $game->getTeam2() ? $game->getTeam2()->getName() : 'Team 2';

            $subject = '💰 Refund Processed - Dev Esports';
            $htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: bold; }
        .content { padding: 40px 30px; }
        .refund-box { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #22c55e; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .refund-amount { font-size: 32px; font-weight: bold; color: #15803d; margin: 10px 0; }
        .detail-box { background: #f5f5f5; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .detail-label { font-size: 12px; color: #666; text-transform: uppercase; }
        .detail-value { font-size: 16px; font-weight: bold; color: #333; margin-top: 5px; }
        .footer { text-align: center; padding: 30px 20px; color: #666; font-size: 12px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Refund Processed!</h1>
            <p>Your refund has been successfully processed</p>
        </div>
        <div class="content">
            <p>Your refund has been initiated and will appear in your account within 3-5 business days.</p>
            
            <div class="refund-box">
                <div style="color: #15803d; font-weight: bold;">✓ Refund Confirmed</div>
                <div class="refund-amount">\$$refundAmount</div>
            </div>
            
            <div class="detail-box">
                <div class="detail-label">💎 Match</div>
                <div class="detail-value">$team1Name vs $team2Name</div>
            </div>
            
            <div class="detail-box">
                <div class="detail-label">📋 Original Order</div>
                <div class="detail-value">DEV-{$payment->getId()}</div>
            </div>
            
            <p style="color: #666; font-size: 14px; line-height: 1.6;">
                <strong>Next Steps:</strong><br>
                The refund will appear as a credit on your original payment method. If you don't see it within 5 business days, please contact your bank.
            </p>
        </div>
        <div class="footer">
            <p><strong>Dev Esports</strong></p>
            <p>© 2026 Dev Esports. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;

            $this->send($payment->getCustomerEmail(), $subject, $htmlContent);
            $this->logger->info('Refund confirmation email sent', ['to' => $payment->getCustomerEmail()]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send refund confirmation', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Render payment confirmation HTML
     */
    private function renderPaymentConfirmationHtml(Payment $payment, $ticket, $game): string
    {
        $team1Name = $game->getTeam1() ? $game->getTeam1()->getName() : 'Team 1';
        $team2Name = $game->getTeam2() ? $game->getTeam2()->getName() : 'Team 2';
        $matchDate = $game->getMatchdate() ? $game->getMatchdate()->format('F j, Y - H:i') : 'TBD';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #b622d1 0%, #7c3aed 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 40px 30px;
        }
        .ticket-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
            border-left: 4px solid #00d9ff;
        }
        .match-title {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .match-title .vs {
            color: #00d9ff;
            font-size: 18px;
        }
        .match-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .detail-item {
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
        }
        .detail-label {
            font-size: 12px;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 16px;
            font-weight: bold;
            color: #00d9ff;
        }
        .price-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, rgba(182, 34, 209, 0.1) 0%, rgba(0, 217, 255, 0.1) 100%);
            border-radius: 8px;
        }
        .price-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .price {
            font-size: 36px;
            font-weight: bold;
            color: #b622d1;
            margin: 10px 0 0 0;
        }
        .confirmation-number {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
        }
        .confirmation-number label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .confirmation-number code {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #b622d1 0%, #00d9ff 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 30px 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
        .footer p {
            margin: 5px 0;
        }
        @media (max-width: 600px) {
            .container {
                max-width: 100%;
                margin: 0;
            }
            .match-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 Ticket Confirmed!</h1>
            <p>Your esports ticket is ready</p>
        </div>
        
        <div class="content">
            <p>Thank you for your purchase! Your ticket has been successfully secured.</p>
            
            <div class="ticket-card">
                <div class="match-title">
                    <span>$team1Name</span>
                    <span class="vs">vs</span>
                    <span>$team2Name</span>
                </div>
                
                <div class="match-details">
                    <div class="detail-item">
                        <div class="detail-label">📅 Date</div>
                        <div class="detail-value">$matchDate</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">🎫 Quantity</div>
                        <div class="detail-value">{$payment->getQuantityPurchased()} Ticket(s)</div>
                    </div>
                </div>
            </div>
            
            <div class="price-section">
                <div class="price-label">Amount Paid</div>
                <div class="price">\${$payment->getAmount()}</div>
            </div>
            
            <div class="confirmation-number">
                <label>📋 Order Number</label>
                <code>DEV-{$payment->getId()}</code>
            </div>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="https://dev-esports.local/dashboard" class="button">View My Tickets</a>
            </p>
            
            <p style="color: #666; font-size: 14px; line-height: 1.6;">
                <strong>What's next?</strong><br>
                Your ticket confirmation has been saved to your account. You can access it anytime from your dashboard. 
                Make sure to bring a valid ID to the event!
            </p>
        </div>
        
        <div class="footer">
            <p><strong>Dev Esports</strong></p>
            <p>Your ultimate esports tournament platform</p>
            <p style="margin-top: 15px; opacity: 0.7;">© 2026 Dev Esports. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
