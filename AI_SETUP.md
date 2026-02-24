# AI INTEGRATION SETUP GUIDE

## 1. SETUP OpenAI/Claude API CLIENT

### Install OpenAI PHP Client

```bash
composer require openai-php/client symfony/http-client
```

OR for Claude (Anthropic):

```bash
composer require anthropic-sdk/sdk
```

---

## 2. OPENAI SETUP (ChatGPT/GPT-4)

### Get API Key

1. Go to https://platform.openai.com
2. Create account and generate API key
3. Add to `.env`:

```
OPENAI_API_KEY=sk-xxxxx
OPENAI_MODEL=gpt-4o-mini
```

### Create AI Service

File: `src/Service/AiAnalysisService.php`

```php
<?php

namespace App\Service;

use OpenAI\Client;
use App\Entity\MatchStatistic;
use App\Entity\Game;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;

class AiAnalysisService
{
    private Client $client;
    private string $model;

    public function __construct(
        string $openaiApiKey,
        string $model = 'gpt-4o-mini',
        private EntityManagerInterface $entityManager
    ) {
        $this->client = \OpenAI::client($openaiApiKey);
        $this->model = $model;
    }

    /**
     * AI: Predict match outcome based on team statistics
     */
    public function predictMatchOutcome(Game $game): array
    {
        $team1Stats = $this->getTeamStats($game->getTeam1());
        $team2Stats = $this->getTeamStats($game->getTeam2());

        $prompt = sprintf(
            "Analyze these esports teams and predict the match outcome:

            Team 1: %s
            - Win Rate: %s%%
            - Avg Score: %s
            - Recent Performance: %s

            Team 2: %s
            - Win Rate: %s%%
            - Avg Score: %s
            - Recent Performance: %s

            Provide:
            1. Predicted winner (0-100%%)
            2. Key factors influencing prediction
            3. Confidence level
            4. Recommended betting odds

            Format as JSON.",
            $game->getTeam1()->getName(),
            $team1Stats['winRate'],
            $team1Stats['avgScore'],
            $team1Stats['recentForm'],
            $game->getTeam2()->getName(),
            $team2Stats['winRate'],
            $team2Stats['avgScore'],
            $team2Stats['recentForm']
        );

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        return json_decode($response->content[0]->text, true);
    }

    /**
     * AI: Generate match summary/commentary
     */
    public function generateMatchCommentary(Game $game, MatchStatistic $stats): string
    {
        $prompt = sprintf(
            "Generate an exciting esports match commentary summary:

            Match: %s vs %s
            Final Score: %s
            Duration: %s minutes
            MVP: %s with %s score
            Location: %s

            Write a detailed 3-4 sentence commentary highlighting key moments.",
            $game->getTeam1()->getName(),
            $game->getTeam2()->getName(),
            $stats->getFinalScore(),
            $stats->getDuration(),
            $stats->getMvpName(),
            $stats->getMvpScore(),
            $game->getTournament()->getLocation()
        );

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->content[0]->text;
    }

    /**
     * AI: Analyze player performance trends
     */
    public function analyzePlayerPerformance(int $playerId): array
    {
        // Get player stats from database
        $stats = $this->entityManager->getRepository('App:MatchStatistic')
            ->findBy(['playerName' => $playerId], ['createdAt' => 'DESC'], 10);

        $statsData = array_map(fn($s) => [
            'date' => $s->getCreatedAt(),
            'score' => $s->getFinalScore(),
            'kills' => $s->getKills() ?? 0,
            'deaths' => $s->getDeaths() ?? 0,
        ], $stats);

        $prompt = sprintf(
            "Analyze this esports player's performance trend over last 10 games:

            %s

            Provide:
            1. Performance trend (improving/declining/stable)
            2. Strengths and weaknesses
            3. Recommendations for improvement
            4. Predicted next game score range

            Be specific and data-driven.",
            json_encode($statsData, JSON_PRETTY_PRINT)
        );

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return [
            'analysis' => $response->content[0]->text,
            'timestamp' => now(),
        ];
    }

    /**
     * AI: Ticket demand forecasting
     */
    public function forecastTicketDemand(Game $game): array
    {
        $historicalData = $this->getHistoricalTicketData($game);

        $prompt = sprintf(
            "Forecast ticket demand for this esports event:

            Event: %s vs %s
            Date: %s
            Location: %s
            Capacity: %s
            Historical Demand Pattern:
            %s

            Provide:
            1. Predicted occupancy %% (0-100)
            2. Days until sold out (if applicable)
            3. Recommended pricing strategy
            4. Risk assessment

            Format as JSON.",
            $game->getTeam1()->getName(),
            $game->getTeam2()->getName(),
            $game->getScheduledDate()?->format('Y-m-d H:i'),
            $game->getTournament()->getLocation(),
            $historicalData['capacity'],
            json_encode($historicalData['pattern'])
        );

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        return json_decode($response->content[0]->text, true);
    }

    /**
     * AI: Recommend ticket prices dynamically
     */
    public function recommendDynamicPricing(Ticket $ticket): array
    {
        $occupancy = ($ticket->getSold() / $ticket->getQuantity()) * 100;
        $daysUntilEvent = $ticket->getGame()->getScheduledDate()?->diff(now())->days;

        $prompt = sprintf(
            "Recommend optimal ticket pricing for this esports event:

            Current Price: $%s
            Current Occupancy: %s%%
            Days Until Event: %s
            Ticket Type: %s
            Capacity: %s

            Consider:
            - Supply and demand
            - Time sensitivity
            - Competitor pricing
            - Revenue optimization

            Provide:
            1. Recommended price
            2. Price change %% (positive or negative)
            3. Expected new occupancy %%
            4. Revenue impact estimate

            Format as JSON.",
            $ticket->getPrice(),
            number_format($occupancy, 1),
            $daysUntilEvent,
            $ticket->getType(),
            $ticket->getQuantity()
        );

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        return json_decode($response->content[0]->text, true);
    }

    /**
     * AI: Customer support chatbot
     */
    public function answerCustomerQuestion(string $question): string
    {
        $systemPrompt = "You are a helpful Dev Esports customer support agent.
                        You help with questions about matches, tickets, and event information.
                        Be professional and concise.";

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $question],
            ],
            'max_tokens' => 500,
        ]);

        return $response->content[0]->text;
    }

    // ============ HELPER METHODS ============

    private function getTeamStats(?object $team): array
    {
        if (!$team) {
            return [
                'winRate' => 50,
                'avgScore' => 0,
                'recentForm' => 'Unknown',
            ];
        }

        $stats = $this->entityManager->getRepository('App:MatchStatistic')
            ->findBy(['team' => $team], ['createdAt' => 'DESC'], 10);

        $wins = count(array_filter($stats, fn($s) => $s->getWon() === true));
        $avgScore = array_reduce($stats, fn($sum, $s) => $sum + $s->getFinalScore(), 0) / (count($stats) ?: 1);

        return [
            'winRate' => (count($stats) > 0 ? ($wins / count($stats)) * 100 : 50),
            'avgScore' => round($avgScore, 2),
            'recentForm' => count($stats) > 0 ? ($wins / count($stats) > 0.6 ? 'Strong' : 'Weak') : 'Unknown',
        ];
    }

    private function getHistoricalTicketData(Game $game): array
    {
        $tickets = $game->getTickets();
        $totalCapacity = $tickets->reduce(fn($carry, $t) => $carry + $t->getQuantity(), 0);
        $totalSold = $tickets->reduce(fn($carry, $t) => $carry + $t->getSold(), 0);

        return [
            'capacity' => $totalCapacity,
            'sold' => $totalSold,
            'occupancy' => ($totalCapacity > 0 ? ($totalSold / $totalCapacity) * 100 : 0),
            'pattern' => [
                'avgOccupancy' => 70,
                'peakDaysBeforeEvent' => 3,
            ],
        ];
    }
}
```

### Register AI Service (config/services.yaml)

```yaml
services:
  App\Service\AiAnalysisService:
    arguments:
      $openaiApiKey: "%env(OPENAI_API_KEY)%"
      $model: "%env(OPENAI_MODEL)%"
```

---

## 3. CREATE AI CONTROLLER

File: `src/Controller/AiController.php`

```php
<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\MatchStatistic;
use App\Entity\Ticket;
use App\Service\AiAnalysisService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai')]
class AiController extends AbstractController
{
    public function __construct(private AiAnalysisService $aiService) {}

    #[Route('/predict-match/{id}', methods: ['GET'])]
    public function predictMatch(Game $game): JsonResponse
    {
        $prediction = $this->aiService->predictMatchOutcome($game);
        return $this->json($prediction);
    }

    #[Route('/match-commentary/{id}', methods: ['GET'])]
    public function matchCommentary(MatchStatistic $stat): JsonResponse
    {
        $game = $stat->getGame();
        $commentary = $this->aiService->generateMatchCommentary($game, $stat);
        return $this->json(['commentary' => $commentary]);
    }

    #[Route('/player-analysis/{playerId}', methods: ['GET'])]
    public function playerAnalysis(int $playerId): JsonResponse
    {
        $analysis = $this->aiService->analyzePlayerPerformance($playerId);
        return $this->json($analysis);
    }

    #[Route('/ticket-forecast/{id}', methods: ['GET'])]
    public function ticketForecast(Game $game): JsonResponse
    {
        $forecast = $this->aiService->forecastTicketDemand($game);
        return $this->json($forecast);
    }

    #[Route('/pricing-recommendation/{id}', methods: ['GET'])]
    public function pricingRecommendation(Ticket $ticket): JsonResponse
    {
        $recommendation = $this->aiService->recommendDynamicPricing($ticket);
        return $this->json($recommendation);
    }

    #[Route('/chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $answer = $this->aiService->answerCustomerQuestion($data['question']);
        return $this->json(['answer' => $answer]);
    }
}
```

---

## 4. ADD AI ENDPOINTS TO ADMIN TEMPLATES

### Update Match Show Template

Add this section to `templates/admin/match/show.html.twig`:

```twig
<!-- AI Predictions Section -->
<div class="card mt-4" style="border-color: var(--secondary)">
    <div class="card-header" style="background-color: var(--bg-tertiary)">
        <h5 class="mb-0">
            <i class="fas fa-brain"></i> AI Predictions
        </h5>
    </div>
    <div class="card-body">
        <button class="btn btn-outline-secondary" id="getPrediction">
            Get AI Prediction
        </button>
        <div id="predictionResult" class="mt-3"></div>
    </div>
</div>

<script>
document.getElementById('getPrediction')?.addEventListener('click', async function() {
    const response = await fetch(`/api/ai/predict-match/{{ game.id }}`);
    const data = await response.json();
    document.getElementById('predictionResult').innerHTML = `
        <p><strong>Winner:</strong> ${Object.keys(data)[0]}</p>
        <p><strong>Confidence:</strong> ${data.confidence_level}%</p>
    `;
});
</script>
```

---

## 5. ENVIRONMENT SETUP

Add to `.env`:

```
# OpenAI
OPENAI_API_KEY=sk-xxxxx
OPENAI_MODEL=gpt-4o-mini

# Alternative: Claude/Anthropic
ANTHROPIC_API_KEY=sk-ant-xxxxx
```

---

## IMPLEMENTATION CHECKLIST

- [ ] Install OpenAI SDK
- [ ] Create AiAnalysisService
- [ ] Create AiController
- [ ] Register services in config/services.yaml
- [ ] Add API keys to .env
- [ ] Test prediction endpoints
- [ ] Add AI buttons to templates
- [ ] Implement caching for AI results (optional)
- [ ] Add rate limiting for AI API calls

---

## PRODUCTION NOTES

1. **Rate Limiting**: Implement request throttling to avoid high API costs
2. **Caching**: Cache AI results to reduce API calls
3. **Monitoring**: Track AI API usage and costs
4. **Error Handling**: Gracefully handle AI service failures
5. **Security**: Never expose API keys in frontend code
