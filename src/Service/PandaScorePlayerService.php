<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Panda Score Player Service
 * Fetches player statistics and reputation scores from Panda Score API
 */
class PandaScorePlayerService
{
    private const PANDA_SCORE_URL = 'https://api.pandascore.co';
    private const TIMEOUT = 10;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $pandaScoreApiKey
    ) {
    }

    /**
     * Get player reputation score from Panda Score
     * Returns a score between 0-100
     * 
     * @param string $playerName Player name to search for
     * @return int Reputation score (0-100)
     */
    public function getPlayerReputationScore(string $playerName): int
    {
        try {
            // Search for player
            $searchUrl = self::PANDA_SCORE_URL . '/players?filter[name]=' . urlencode($playerName);
            
            $response = $this->httpClient->request('GET', $searchUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->pandaScoreApiKey,
                    'Accept' => 'application/json',
                ],
                'timeout' => self::TIMEOUT,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logger->warning('Panda Score API error', [
                    'status' => $response->getStatusCode(),
                    'player' => $playerName,
                ]);
                return 50; // Default neutral score
            }

            $data = $response->toArray();
            
            if (empty($data)) {
                $this->logger->info('Player not found in Panda Score', ['player' => $playerName]);
                return 50; // Default neutral score for unknown players
            }

            // Get first player result
            $player = $data[0] ?? null;
            if (!$player) {
                return 50;
            }

            // Calculate reputation score from player stats
            $score = $this->calculateReputationScore($player);
            
            $this->logger->info('Panda Score reputation calculated', [
                'player' => $playerName,
                'score' => $score,
            ]);

            return $score;

        } catch (\Exception $e) {
            $this->logger->error('Error fetching Panda Score reputation', [
                'error' => $e->getMessage(),
                'player' => $playerName,
            ]);
            return 50; // Safe default
        }
    }

    /**
     * Calculate reputation score from player statistics
     * Factors: win rate, matches played, rating, suspensions
     */
    private function calculateReputationScore(array $player): int
    {
        $score = 50; // Base score

        // Win rate (max +30)
        if (isset($player['stats']['win_rate'])) {
            $winRate = $player['stats']['win_rate'];
            $score += min(30, $winRate * 0.3);
        }

        // Matches played (max +15)
        if (isset($player['stats']['matches'])) {
            $matches = $player['stats']['matches'];
            // More matches = more credibility
            $score += min(15, log($matches + 1) * 5);
        }

        // Rating/ELO (max +20)
        if (isset($player['stats']['rating'])) {
            $rating = $player['stats']['rating'];
            // Normalize rating to 0-20 range
            $normalizedRating = min(20, ($rating / 5000) * 20);
            $score += $normalizedRating;
        }

        // Penalties for suspensions/bans (max -25)
        $penalties = 0;
        if (isset($player['suspension'])) {
            $penalties -= 15;
        }
        if (isset($player['bans']) && count($player['bans']) > 0) {
            $penalties -= 10 * count($player['bans']);
        }
        
        $score += max(-25, $penalties);

        // Ensure score is between 0-100
        return max(0, min(100, intval($score)));
    }

    /**
     * Get multiple players' scores
     * Useful for team-based analysis
     */
    public function getPlayersReputationScores(array $playerNames): array
    {
        $scores = [];
        foreach ($playerNames as $name) {
            $scores[$name] = $this->getPlayerReputationScore($name);
        }
        return $scores;
    }
}
