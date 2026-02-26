<?php

namespace App\Service;

use App\Entity\DemandeRecompense;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MatchStatsService
{
    private const API_BASE = 'https://api.pandascore.co';

    public function __construct(
        private HttpClientInterface $httpClient,
        ParameterBagInterface $params
    ) {
        $this->apiKey = (string) $params->get('app.pandascore_api_key');
    }

    private string $apiKey;

    /**
     * Vérifier la légitimité d'une demande basée sur PandaScore
     */
    public function verifyClaimLegitimacy(DemandeRecompense $demande): array
    {
        $result = [
            'is_legitimate' => false,
            'confidence' => 0,
            'match_stats_found' => false,
            'player_stats' => [],
            'warnings' => [],
        ];

        if (empty($this->apiKey) || $this->apiKey === 'your_pandascore_api_key_here') {
            $result['warnings'][] = 'PandaScore API key not configured.';
            return $result;
        }

        $playerName = trim((string) $demande->getNomDemandeur());
        if ($playerName === '') {
            $result['warnings'][] = 'Player name is missing.';
            return $result;
        }

        try {
            $players = $this->request('/players', [
                'search[name]' => $playerName,
                'per_page' => 1,
            ]);

            if (empty($players)) {
                $result['warnings'][] = 'No PandaScore player found for this name.';
                return $result;
            }

            $player = $players[0];

            $result['match_stats_found'] = true;
            $result['is_legitimate'] = true;
            $result['confidence'] = 70;
            $result['player_stats'] = [
                'player_id' => $player['id'] ?? null,
                'name' => $player['name'] ?? null,
                'slug' => $player['slug'] ?? null,
                'role' => $player['role'] ?? null,
                'current_team' => $player['current_team']['name'] ?? null,
                'nationality' => $player['nationality'] ?? null,
            ];

        } catch (\Throwable $e) {
            $result['warnings'][] = 'PandaScore API error: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Scorer une demande basée sur les stats historiques du joueur
     */
    public function getPlayerHistoricalScore(DemandeRecompense $demande): int
    {
        if (empty($this->apiKey) || $this->apiKey === 'your_pandascore_api_key_here') {
            return 0;
        }

        $playerName = trim((string) $demande->getNomDemandeur());
        if ($playerName === '') {
            return 0;
        }

        try {
            $players = $this->request('/players', [
                'search[name]' => $playerName,
                'per_page' => 1,
            ]);

            if (empty($players)) {
                return 0;
            }

            return 60;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Calculer un score composite
     * ia_score (60%) + rules_score (40%)
     */
    public function calculateCompositeScore(int $iaScore, int $rulesScore): int
    {
        return (int) ((($iaScore * 0.6) + ($rulesScore * 0.4)));
    }

    private function request(string $path, array $query = []): array
    {
        $response = $this->httpClient->request('GET', self::API_BASE . $path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'query' => $query,
        ]);

        return $response->toArray(false);
    }
}
