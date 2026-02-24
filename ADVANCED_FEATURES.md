# ADVANCED BUSINESS FEATURES IMPLEMENTATION

## 1. REAL-TIME MATCH SCORE UPDATES (WebSocket)

### Install WebSocket Bundle

```bash
composer require symfony/symfony:^7
composer require gos/web-socket-bundle
```

### Configure WebSockets (config/packages/gos_web_socket.yaml)

```yaml
gos_web_socket:
  server:
    host: 127.0.0.1
    port: 8080
    keepalive_interval: 30
  origins:
    - "*"
```

### Create WebSocket Handler

File: `src/WebSocket/MatchUpdateHandler.php`

```php
<?php

namespace App\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class MatchUpdateHandler implements MessageComponentInterface
{
    private \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);

        if ($data->type === 'scoreUpdate') {
            // Broadcast score update to all connected clients
            foreach ($this->clients as $client) {
                $client->send(json_encode([
                    'type' => 'scoreUpdate',
                    'matchId' => $data->matchId,
                    'score' => $data->score,
                    'timestamp' => now()->toIso8601String()
                ]));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {}

    public function onError(ConnectionInterface $conn, \Exception $e) {}
}
```

### Update Match in Real-Time

File: `src/Controller/MatchAdminController.php` (update save method):

```php
#[Route('/{id}/update-score', name: 'admin_match_update_score', methods: 'POST')]
public function updateScore(Game $game, Request $request): Response
{
    $data = json_decode($request->getContent(), true);

    // Update score
    $game->setTeam1Score((int)$data['team1_score']);
    $game->setTeam2Score((int)$data['team2_score']);
    $game->setStatus('ongoing');

    $this->entityManager->flush();

    // Broadcast via WebSocket
    file_get_contents('http://127.0.0.1:8080/broadcast', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'content' => json_encode([
                'type' => 'scoreUpdate',
                'matchId' => $game->getId(),
                'team1_score' => $game->getTeam1Score(),
                'team2_score' => $game->getTeam2Score(),
                'timestamp' => (new \DateTime())->toIso8601String()
            ])
        ]
    ]));

    return $this->json(['success' => true]);
}
```

---

## 2. AUTOMATIC MATCH STATUS PROGRESSION

### Create State Machine Service

File: `src/Service/MatchStateService.php`

```php
<?php

namespace App\Service;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class MatchStateService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * Auto-progress match status based on scheduled date
     */
    public function progressMatchStatus(Game $game): string
    {
        $now = new \DateTime();
        $scheduled = $game->getScheduledDate();
        $duration = $game->getDuration() ?: 60; // Default 60 minutes

        // Pending: before scheduled date
        if ($now < $scheduled) {
            $game->setStatus('pending');
            return 'pending';
        }

        // Ongoing: started but not finished
        if ($now < $scheduled->modify("+{$duration} minutes")) {
            if ($game->getStatus() !== 'ongoing') {
                $game->setStatus('ongoing');
            }
            return 'ongoing';
        }

        // Finished: exceeded scheduled + duration
        if ($game->getStatus() !== 'finished') {
            $game->setStatus('finished');
        }
        return 'finished';
    }

    /**
     * Process all matches and update statuses (run via cron)
     */
    public function processAllMatches(): int
    {
        $games = $this->entityManager->getRepository(Game::class)->findAll();
        $updated = 0;

        foreach ($games as $game) {
            $oldStatus = $game->getStatus();
            $this->progressMatchStatus($game);

            if ($oldStatus !== $game->getStatus()) {
                $updated++;
            }
        }

        $this->entityManager->flush();
        return $updated;
    }
}
```

### Create Cron Command

File: `src/Command/ProcessMatchStatusCommand.php`

```php
<?php

namespace App\Command;

use App\Service\MatchStateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:process-match-status',
    description: 'Update match statuses automatically'
)]
class ProcessMatchStatusCommand extends Command
{
    public function __construct(private MatchStateService $matchStateService) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updated = $this->matchStateService->processAllMatches();
        $output->writeln("Updated $updated matches");
        return Command::SUCCESS;
    }
}
```

### Schedule Cron Job (config/packages/scheduler.yaml)

```yaml
when@prod:
  framework:
    scheduler:
      default_timezone: "UTC"
      tasks:
        process_match_status:
          description: "Update match statuses"
          expression: "*/5 * * * *" # Every 5 minutes
          command: "app:process-match-status"
```

---

## 3. DYNAMIC TICKET PRICING

### Create Dynamic Pricing Service

File: `src/Service/DynamicPricingService.php`

```php
<?php

namespace App\Service;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;

class DynamicPricingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AiAnalysisService $aiService
    ) {}

    /**
     * Calculate dynamic price based on demand
     */
    public function calculateDynamicPrice(Ticket $ticket): float
    {
        $basePrice = $ticket->getBasePrice() ?? $ticket->getPrice();
        $occupancy = ($ticket->getSold() / $ticket->getQuantity()) * 100;
        $daysUntilEvent = $ticket->getGame()?->getScheduledDate()?->diff(new \DateTime())->days ?? 30;

        // Price multiplier based on occupancy
        $occupancyMultiplier = match (true) {
            $occupancy >= 90 => 1.5,   // Last 10% = 50% higher
            $occupancy >= 75 => 1.3,   // 75-90% = 30% higher
            $occupancy >= 50 => 1.1,   // 50-75% = 10% higher
            $occupancy >= 25 => 1.0,   // 25-50% = base price
            default => 0.85            // < 25% = 15% discount
        };

        // Time multiplier (cheaper early, expensive closer to event)
        $timeMultiplier = match (true) {
            $daysUntilEvent > 30 => 0.8,
            $daysUntilEvent > 14 => 0.9,
            $daysUntilEvent > 7 => 1.0,
            $daysUntilEvent > 3 => 1.2,
            default => 1.5
        };

        return round($basePrice * $occupancyMultiplier * $timeMultiplier, 2);
    }

    /**
     * Update all ticket prices for a game
     */
    public function updateGameTicketPrices(\App\Entity\Game $game): void
    {
        foreach ($game->getTickets() as $ticket) {
            $newPrice = $this->calculateDynamicPrice($ticket);
            $ticket->setPrice($newPrice);
        }

        $this->entityManager->flush();
    }

    /**
     * Apply AI-recommended pricing
     */
    public function applyAiPricing(Ticket $ticket): void
    {
        $recommendation = $this->aiService->recommendDynamicPricing($ticket);

        if (isset($recommendation['recommended_price'])) {
            $ticket->setPrice((float)$recommendation['recommended_price']);
            $this->entityManager->flush();
        }
    }
}
```

---

## 4. PERFORMANCE ANALYTICS DASHBOARD

### Create Analytics Controller

File: `src/Controller/AnalyticsController.php`

```php
<?php

namespace App\Controller;

use App\Repository\MatchStatisticRepository;
use App\Repository\GameRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/analytics')]
class AnalyticsController extends AbstractController
{
    public function __construct(
        private MatchStatisticRepository $statsRepo,
        private GameRepository $gameRepo,
        private TicketRepository $ticketRepo
    ) {}

    #[Route('', name: 'admin_analytics')]
    public function dashboard(): Response
    {
        // Match Statistics
        $matchStats = [
            'total' => $this->gameRepo->count([]),
            'finished' => $this->gameRepo->count(['status' => 'finished']),
            'ongoing' => $this->gameRepo->count(['status' => 'ongoing']),
            'pending' => $this->gameRepo->count(['status' => 'pending']),
        ];

        // Revenue Analytics
        $ticketRevenue = array_reduce(
            $this->ticketRepo->findAll(),
            fn($sum, $t) => $sum + ($t->getPrice() * $t->getSold()),
            0
        );

        // Top Performers
        $topPlayers = $this->statsRepo->findTopPerformers(5);

        // Win Rates by Team
        $teamStats = $this->statsRepo->getTeamWinRates();

        return $this->render('admin/analytics/dashboard.html.twig', [
            'matchStats' => $matchStats,
            'ticketRevenue' => $ticketRevenue,
            'topPlayers' => $topPlayers,
            'teamStats' => $teamStats,
        ]);
    }

    #[Route('/player/{id}', name: 'admin_player_analytics')]
    public function playerAnalytics(int $id): Response
    {
        $stats = $this->statsRepo->findByPlayer($id);

        return $this->render('admin/analytics/player.html.twig', [
            'stats' => $stats,
        ]);
    }
}
```

---

## 5. TOURNAMENT BRACKET GENERATION

### Create Bracket Service

File: `src/Service/BracketGeneratorService.php`

```php
<?php

namespace App\Service;

use App\Entity\Tournament;

class BracketGeneratorService
{
    /**
     * Generate single-elimination bracket
     */
    public function generateSingleElimination(array $teams): array
    {
        $teamCount = count($teams);
        $rounds = ceil(log($teamCount, 2));

        $bracket = [
            'rounds' => [],
            'totalTeams' => $teamCount,
        ];

        // First round matches
        $matches = [];
        for ($i = 0; $i < $teamCount; $i += 2) {
            $matches[] = [
                'team1' => $teams[$i] ?? null,
                'team2' => $teams[$i + 1] ?? null,
                'score1' => null,
                'score2' => null,
                'winner' => null,
            ];
        }

        $bracket['rounds'][] = $matches;

        return $bracket;
    }

    /**
     * Generate round-robin bracket
     */
    public function generateRoundRobin(array $teams): array
    {
        $teamCount = count($teams);
        $rounds = $teamCount - 1;
        $bracket = ['rounds' => []];

        for ($r = 0; $r < $rounds; $r++) {
            $roundMatches = [];
            for ($i = 0; $i < floor($teamCount / 2); $i++) {
                $t1 = ($r + $i) % ($teamCount - 1);
                $t2 = ($r + $teamCount - 1 - $i) % ($teamCount - 1);

                if ($t2 == $teamCount - 1) $t2 = $teamCount - 1;

                $roundMatches[] = [
                    'team1' => $teams[$t1] ?? null,
                    'team2' => $teams[$t2] ?? null,
                ];
            }
            $bracket['rounds'][] = $roundMatches;
        }

        return $bracket;
    }
}
```

---

## QUICK START CHECKLIST

✅ **Week 1 - Bundles & APIs**

- [ ] Install API Platform bundle
- [ ] Configure Stripe payment integration
- [ ] Set up SendGrid email notifications
- [ ] Test payment flow

✅ **Week 2 - Advanced Features**

- [ ] Implement WebSocket real-time scores
- [ ] Create match status automation
- [ ] Build dynamic pricing system
- [ ] Create analytics dashboard

✅ **Week 3 - AI Integration**

- [ ] Set up OpenAI API
- [ ] Create prediction engine
- [ ] Implement ticket forecasting
- [ ] Build customer support chatbot

✅ **Week 4 - Testing & Deployment**

- [ ] Test all APIs and integrations
- [ ] Performance optimization
- [ ] Security audit
- [ ] Production deployment

---

## USEFUL COMMANDS

```bash
# Test AI predictions
php bin/console app:ai:predict-matches

# Update match statuses
php bin/console app:process-match-status

# Generate brackets
php bin/console app:generate-brackets

# Clear cache
php bin/console cache:clear

# Run tests
./bin/phpunit
```
