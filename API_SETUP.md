# API INTEGRATION SETUP GUIDE

## 1. PAYMENT API - STRIPE INTEGRATION

### Install Stripe SDK

```bash
composer require stripe/stripe-php stripe/flask
```

### Create Stripe Service

File: `src/Service/StripeService.php`

```php
<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;

class StripeService
{
    private string $stripeApiKey;

    public function __construct(string $stripeApiKey)
    {
        $this->stripeApiKey = $stripeApiKey;
        Stripe::setApiKey($this->stripeApiKey);
    }

    /**
     * Create a payment intent for ticket purchase
     */
    public function createPaymentIntent(
        float $amount,
        string $currency,
        string $ticketNumber,
        string $customerEmail
    ): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => (int)($amount * 100), // Stripe uses cents
            'currency' => $currency,
            'description' => "Ticket: {$ticketNumber}",
            'receipt_email' => $customerEmail,
            'metadata' => [
                'ticket_number' => $ticketNumber,
                'customer_email' => $customerEmail,
            ],
        ]);
    }

    /**
     * Retrieve payment intent
     */
    public function getPaymentIntent(string $intentId): PaymentIntent
    {
        return PaymentIntent::retrieve($intentId);
    }

    /**
     * Process refund
     */
    public function refundPayment(string $paymentIntentId, float $amount = null)
    {
        $intent = $this->getPaymentIntent($paymentIntentId);

        if ($intent->charges->data) {
            $chargeId = $intent->charges->data[0]->id;

            return \Stripe\Refund::create([
                'charge' => $chargeId,
                'amount' => $amount ? (int)($amount * 100) : null,
            ]);
        }
    }
}
```

### Register Service (config/services.yaml)

```yaml
services:
  App\Service\StripeService:
    arguments:
      $stripeApiKey: "%env(STRIPE_SECRET_KEY)%"
```

### Environment Variables (.env)

```
STRIPE_PUBLIC_KEY=pk_test_xxxxx
STRIPE_SECRET_KEY=sk_test_xxxxx
```

### Create Ticket Payment Controller

File: `src/Controller/TicketPaymentController.php`

```php
<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TicketPaymentController extends AbstractController
{
    public function __construct(private StripeService $stripeService) {}

    #[Route('/api/tickets/{id}/create-payment', methods: ['POST'])]
    public function createPayment(Ticket $ticket, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $intent = $this->stripeService->createPaymentIntent(
            $ticket->getPrice(),
            'usd',
            $ticket->getTicketNumber(),
            $data['email'] ?? 'customer@example.com'
        );

        return $this->json([
            'clientSecret' => $intent->client_secret,
            'status' => $intent->status,
        ]);
    }

    #[Route('/api/tickets/{id}/confirm-payment', methods: ['POST'])]
    public function confirmPayment(Ticket $ticket, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $intent = $this->stripeService->getPaymentIntent($data['paymentIntentId']);

        if ($intent->status === 'succeeded') {
            // Mark ticket as sold
            $ticket->setStatus('sold');
            $this->getDoctrine()->getManager()->flush();

            return $this->json(['success' => true, 'message' => 'Ticket purchased!']);
        }

        return $this->json(['success' => false, 'message' => 'Payment failed'], 400);
    }
}
```

---

## 2. EMAIL NOTIFICATIONS - SENDGRID/MAILGUN

### Install Symfony Mailer

```bash
composer require symfony/mailer symfony/sendgrid-mailer
# OR for Mailgun:
# composer require symfony/mailgun-mailer
```

### Configure (.env)

**For SendGrid:**

```
MAILER_DSN=sendgrid+api://SG.xxxx@default
SENDGRID_API_KEY=SG.xxxx
```

**For Mailgun:**

```
MAILER_DSN=mailgun+https://key@domain
MAILGUN_DOMAIN=mg.example.com
MAILGUN_API_KEY=key-xxx
```

### Create Notification Service

File: `src/Service/NotificationService.php`

```php
<?php

namespace App\Service;

use App\Entity\Game;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(private MailerInterface $mailer) {}

    /**
     * Send match starting notification
     */
    public function notifyMatchStarting(Game $game, array $customerEmails): void
    {
        $email = (new Email())
            ->from('noreply@devesports.com')
            ->to(...$customerEmails)
            ->subject('⚡ Your match is starting soon!')
            ->html(sprintf(
                '<h2>Match Alert</h2>
                 <p>%s vs %s starts at %s</p>
                 <p>Venue: %s</p>',
                $game->getTeam1()?->getName(),
                $game->getTeam2()?->getName(),
                $game->getScheduledDate()?->format('Y-m-d H:i'),
                $game->getTournament()?->getLocation()
            ));

        $this->mailer->send($email);
    }

    /**
     * Send ticket purchase confirmation
     */
    public function notifyTicketPurchase(string $email, string $ticketNumber, float $price): void
    {
        $emailMessage = (new Email())
            ->from('tickets@devesports.com')
            ->to($email)
            ->subject('✓ Ticket Purchase Confirmation')
            ->html(sprintf(
                '<h2>Thank you for your purchase!</h2>
                 <p><strong>Ticket #:</strong> %s</p>
                 <p><strong>Price:</strong> $%s</p>
                 <p>Your ticket has been sent to this email.</p>',
                $ticketNumber,
                number_format($price, 2)
            ));

        $this->mailer->send($emailMessage);
    }
}
```

---

## 3. ESPORTS DATA API - EXAMPLE: HLTV

### Install HTTP Client

```bash
composer require symfony/http-client
```

### Create Esports Stats Service

File: `src/Service/EsportsDataService.php`

```php
<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EsportsDataService
{
    private string $hltvApiUrl = 'https://api.hltv.org';

    public function __construct(private HttpClientInterface $httpClient) {}

    /**
     * Get match statistics from HLTV
     */
    public function getMatchStats(int $matchId): array
    {
        // Note: HLTV API requires API key
        // This is a simplified example

        $response = $this->httpClient->request(
            'GET',
            "{$this->hltvApiUrl}/matches/{$matchId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['HLTV_API_KEY'],
                ]
            ]
        );

        return $response->toArray();
    }

    /**
     * Get player statistics
     */
    public function getPlayerStats(int $playerId): array
    {
        $response = $this->httpClient->request(
            'GET',
            "{$this->hltvApiUrl}/players/{$playerId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['HLTV_API_KEY'],
                ]
            ]
        );

        return $response->toArray();
    }

    /**
     * Get team rankings
     */
    public function getTeamRankings(): array
    {
        $response = $this->httpClient->request(
            'GET',
            "{$this->hltvApiUrl}/rankings/teams",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['HLTV_API_KEY'],
                ]
            ]
        );

        return $response->toArray();
    }
}
```

### Register Service (config/services.yaml)

```yaml
services:
  App\Service\EsportsDataService:
    arguments:
      $httpClient: "@http_client"
```

---

## 4. WEATHER API - FOR TOURNAMENT LOCATIONS

### Install Weather API Integration

```bash
composer require openweathermap-api/openweathermap-api-php
```

### Create Weather Service

File: `src/Service/WeatherService.php`

```php
<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private string $weatherApiUrl = 'https://api.openweathermap.org/data/2.5/weather';
    private string $apiKey;

    public function __construct(
        private HttpClientInterface $httpClient,
        string $openweatherApiKey
    ) {
        $this->apiKey = $openweatherApiKey;
    }

    /**
     * Get weather for tournament location
     */
    public function getWeather(string $city): array
    {
        $response = $this->httpClient->request(
            'GET',
            $this->weatherApiUrl,
            [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]
            ]
        );

        return $response->toArray();
    }

    /**
     * Check if event is weather favorable
     */
    public function isWeatherFavorable(string $city): bool
    {
        $weather = $this->getWeather($city);

        // Example: event is favorable if temp between 10-30C and not raining
        $temp = $weather['main']['temp'] ?? null;
        $weatherMain = $weather['weather'][0]['main'] ?? null;

        return $temp !== null && $temp >= 10 && $temp <= 30 && $weatherMain !== 'Rain';
    }
}
```

---

## NEXT STEPS

1. Install bundles: `composer require api-platform/core`
2. Configure API services with your API keys
3. Add environment variables to `.env`
4. Test APIs in controllers
5. See AI_SETUP.md for AI integration
