<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlayerRecommendationService
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private PlayerScoreService $playerScoreService,
        private HttpClientInterface $httpClient
    ) {}

    /**
     * @return array<int, array{player: Player, aiScore: float, reason: string}>
     */
    public function recommendForTeam(Team $team, int $limit = 3): array
    {
        $candidates = $this->getEligibleCandidates($team);
        $recommendations = $this->buildHeuristicRecommendations($candidates);

        $preselected = array_slice($recommendations, 0, max($limit * 4, 10));

        $geminiRecommendations = $this->recommendWithGemini($team, $preselected, $limit);
        if (!empty($geminiRecommendations)) {
            return $geminiRecommendations;
        }

        return array_slice($recommendations, 0, max(1, $limit));
    }

    /**
     * @return array{
     *   summary: string,
     *   slotsRemaining: int,
     *   recommendedPlayers: array<int, array{player: Player, aiScore: float, reason: string, suggestedRole: string}>
     * }
     */
    public function recommendPerfectTeamPlan(Team $team, int $targetTeamSize = 5): array
    {
        $currentMembers = $team->getPlayers()->toArray();
        $slotsRemaining = max(0, $targetTeamSize - count($currentMembers));

        if ($slotsRemaining <= 0) {
            return [
                'summary' => 'Équipe déjà complète. Aucune recommandation supplémentaire nécessaire.',
                'slotsRemaining' => 0,
                'recommendedPlayers' => [],
            ];
        }

        $candidates = $this->getEligibleCandidates($team);
        $heuristic = $this->buildHeuristicRecommendations($candidates);
        $preselected = array_slice($heuristic, 0, max($slotsRemaining * 4, 12));

        $geminiPlan = $this->recommendPerfectTeamWithGemini($team, $preselected, $slotsRemaining);
        if (!empty($geminiPlan)) {
            return $geminiPlan;
        }

        $fallbackPlayers = array_slice($heuristic, 0, $slotsRemaining);
        $fallbackPlayers = array_map(fn (array $item) => [
            'player' => $item['player'],
            'aiScore' => $item['aiScore'],
            'reason' => $item['reason'],
            'suggestedRole' => $item['player']->getRole() ?: 'Flexible',
        ], $fallbackPlayers);

        return [
            'summary' => 'Plan optimisé avec fallback local: joueurs classés selon score, KDA, expérience et complémentarité de rôles.',
            'slotsRemaining' => $slotsRemaining,
            'recommendedPlayers' => $fallbackPlayers,
        ];
    }

    /**
     * @param array<int, Player> $candidates
     * @return array<int, array{player: Player, aiScore: float, reason: string}>
     */
    private function buildHeuristicRecommendations(array $candidates): array
    {
        $recommendations = [];

        foreach ($candidates as $candidate) {
            $stats = $this->playerScoreService->getPlayerStats($candidate);
            $baseScore = (float) ($stats['totalScore'] ?? 0.0);
            $normalizedBase = min(50.0, $baseScore / 200.0);
            $kdaBonus = min(25.0, ((float) ($stats['avgKDA'] ?? 0.0)) * 5.0);
            $experienceBonus = min(15.0, ((int) ($stats['matchCount'] ?? 0)) * 1.5);
            $roleBonus = !empty($candidate->getRole()) ? 10.0 : 0.0;

            $aiScore = round($normalizedBase + $kdaBonus + $experienceBonus + $roleBonus, 2);

            $reasonParts = [];
            if ((int) ($stats['matchCount'] ?? 0) > 0) {
                $reasonParts[] = 'KDA ' . number_format((float) ($stats['avgKDA'] ?? 0), 2);
                $reasonParts[] = (int) ($stats['matchCount'] ?? 0) . ' matchs';
            } else {
                $reasonParts[] = 'joueur disponible';
            }

            if (!empty($candidate->getRole())) {
                $reasonParts[] = 'rôle: ' . $candidate->getRole();
            }

            $recommendations[] = [
                'player' => $candidate,
                'aiScore' => $aiScore,
                'reason' => implode(' • ', $reasonParts),
            ];
        }

        usort($recommendations, fn (array $a, array $b) => $b['aiScore'] <=> $a['aiScore']);

        return $recommendations;
    }

    /**
     * @return array<int, Player>
     */
    private function getEligibleCandidates(Team $team): array
    {
        $teamPlayers = $team->getPlayers()->toArray();
        $teamPlayerIds = array_map(fn (Player $player) => $player->getId(), $teamPlayers);

        return array_values(array_filter(
            $this->playerRepository->findAll(),
            fn (Player $player) =>
                !in_array($player->getId(), $teamPlayerIds, true)
                && $player->isApproved()
                && strtoupper((string) $player->getPlayerStatus()) !== 'BANNED'
        ));
    }

    /**
     * @param array<int, array{player: Player, aiScore: float, reason: string}> $candidates
     * @return array<int, array{player: Player, aiScore: float, reason: string}>
     */
    private function recommendWithGemini(Team $team, array $candidates, int $limit): array
    {
        $apiKey = $this->getGeminiApiKey();
        if ($apiKey === '' || empty($candidates)) {
            return [];
        }

        $model = $this->getGeminiModel();
        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($model),
            rawurlencode($apiKey)
        );

        $payloadCandidates = array_map(function (array $item): array {
            $player = $item['player'];
            return [
                'player_id' => $player->getId(),
                'nickname' => $player->getNickname(),
                'role' => $player->getRole(),
                'heuristic_score' => $item['aiScore'],
                'heuristic_reason' => $item['reason'],
            ];
        }, $candidates);

        $prompt = "You are an esports recruitment assistant.\n"
            . "Given a team and candidate players, return best recommendations.\n"
            . "Output strict JSON only with this schema:\n"
            . "{\"recommendations\":[{\"player_id\": number, \"ai_score\": number, \"reason\": string}]}\n"
            . "Rules:\n"
            . "- Recommend at most {$limit} players.\n"
            . "- ai_score must be from 0 to 100.\n"
            . "- Prefer diverse roles and high performance.\n"
            . "Team: " . json_encode([
                'team_name' => $team->getName(),
                'team_game' => $team->getJeu(),
                'team_level' => $team->getNiveau(),
            ], JSON_UNESCAPED_UNICODE) . "\n"
            . "Candidates: " . json_encode($payloadCandidates, JSON_UNESCAPED_UNICODE);

        try {
            $response = $this->httpClient->request('POST', $endpoint, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => $prompt,
                        ]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                    ],
                ],
                'timeout' => 12,
            ]);

            if ($response->getStatusCode() >= 400) {
                return [];
            }

            $data = $response->toArray(false);
            $modelText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (!is_string($modelText) || trim($modelText) === '') {
                return [];
            }

            $decoded = json_decode($modelText, true);
            if (!is_array($decoded) || !isset($decoded['recommendations']) || !is_array($decoded['recommendations'])) {
                return [];
            }

            $byPlayerId = [];
            foreach ($candidates as $candidate) {
                $candidatePlayerId = $candidate['player']->getId();
                if ($candidatePlayerId !== null) {
                    $byPlayerId[$candidatePlayerId] = $candidate;
                }
            }

            $final = [];
            foreach ($decoded['recommendations'] as $item) {
                $playerId = isset($item['player_id']) ? (int) $item['player_id'] : 0;
                if ($playerId === 0 || !isset($byPlayerId[$playerId])) {
                    continue;
                }

                $base = $byPlayerId[$playerId];
                $score = isset($item['ai_score']) ? (float) $item['ai_score'] : $base['aiScore'];
                $reason = isset($item['reason']) && is_string($item['reason']) && $item['reason'] !== ''
                    ? $item['reason']
                    : $base['reason'];

                $final[] = [
                    'player' => $base['player'],
                    'aiScore' => round(max(0, min(100, $score)), 2),
                    'reason' => $reason,
                ];
            }

            usort($final, fn (array $a, array $b) => $b['aiScore'] <=> $a['aiScore']);

            return array_slice($final, 0, max(1, $limit));
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param array<int, array{player: Player, aiScore: float, reason: string}> $candidates
     * @return array{
     *   summary: string,
     *   slotsRemaining: int,
     *   recommendedPlayers: array<int, array{player: Player, aiScore: float, reason: string, suggestedRole: string}>
     * }
     */
    private function recommendPerfectTeamWithGemini(Team $team, array $candidates, int $slotsRemaining): array
    {
        $apiKey = $this->getGeminiApiKey();
        if ($apiKey === '' || empty($candidates) || $slotsRemaining <= 0) {
            return [];
        }

        $model = $this->getGeminiModel();
        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($model),
            rawurlencode($apiKey)
        );

        $currentMembers = array_map(fn (Player $player) => [
            'player_id' => $player->getId(),
            'nickname' => $player->getNickname(),
            'role' => $player->getRole(),
        ], $team->getPlayers()->toArray());

        $payloadCandidates = array_map(fn (array $item) => [
            'player_id' => $item['player']->getId(),
            'nickname' => $item['player']->getNickname(),
            'role' => $item['player']->getRole(),
            'heuristic_score' => $item['aiScore'],
            'heuristic_reason' => $item['reason'],
        ], $candidates);

        $prompt = "You are an expert esports team builder.\n"
            . "Goal: build the best possible team composition.\n"
            . "Choose exactly {$slotsRemaining} players to complete the team with role balance and performance synergy.\n"
            . "Output STRICT JSON only with this schema:\n"
            . "{\"team_summary\": string, \"players\":[{\"player_id\": number, \"ai_score\": number, \"reason\": string, \"suggested_role\": string}]}\n"
            . "Rules:\n"
            . "- ai_score in [0, 100]\n"
            . "- prefer role complementarity and strong stats\n"
            . "- reasons must be concise\n"
            . "Team context: " . json_encode([
                'team_name' => $team->getName(),
                'game' => $team->getJeu(),
                'level' => $team->getNiveau(),
                'current_members' => $currentMembers,
            ], JSON_UNESCAPED_UNICODE) . "\n"
            . "Candidates: " . json_encode($payloadCandidates, JSON_UNESCAPED_UNICODE);

        try {
            $response = $this->httpClient->request('POST', $endpoint, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => $prompt,
                        ]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.15,
                        'responseMimeType' => 'application/json',
                    ],
                ],
                'timeout' => 15,
            ]);

            if ($response->getStatusCode() >= 400) {
                return [];
            }

            $data = $response->toArray(false);
            $modelText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (!is_string($modelText) || trim($modelText) === '') {
                return [];
            }

            $decoded = json_decode($modelText, true);
            if (!is_array($decoded) || !isset($decoded['players']) || !is_array($decoded['players'])) {
                return [];
            }

            $byPlayerId = [];
            foreach ($candidates as $candidate) {
                $candidatePlayerId = $candidate['player']->getId();
                if ($candidatePlayerId !== null) {
                    $byPlayerId[$candidatePlayerId] = $candidate;
                }
            }

            $picked = [];
            foreach ($decoded['players'] as $item) {
                $playerId = isset($item['player_id']) ? (int) $item['player_id'] : 0;
                if ($playerId === 0 || !isset($byPlayerId[$playerId])) {
                    continue;
                }

                $base = $byPlayerId[$playerId];
                $picked[] = [
                    'player' => $base['player'],
                    'aiScore' => round(max(0, min(100, (float) ($item['ai_score'] ?? $base['aiScore']))), 2),
                    'reason' => isset($item['reason']) && is_string($item['reason']) && trim($item['reason']) !== ''
                        ? trim($item['reason'])
                        : $base['reason'],
                    'suggestedRole' => isset($item['suggested_role']) && is_string($item['suggested_role']) && trim($item['suggested_role']) !== ''
                        ? trim($item['suggested_role'])
                        : ($base['player']->getRole() ?: 'Flexible'),
                ];
            }

            $picked = array_slice($picked, 0, $slotsRemaining);
            if (empty($picked)) {
                return [];
            }

            $summary = isset($decoded['team_summary']) && is_string($decoded['team_summary']) && trim($decoded['team_summary']) !== ''
                ? trim($decoded['team_summary'])
                : 'Composition IA optimisée pour maximiser performance et complémentarité des rôles.';

            return [
                'summary' => $summary,
                'slotsRemaining' => $slotsRemaining,
                'recommendedPlayers' => $picked,
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    private function getGeminiApiKey(): string
    {
        $value = getenv('GEMINI_API_KEY');
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if (isset($_ENV['GEMINI_API_KEY']) && is_string($_ENV['GEMINI_API_KEY']) && trim($_ENV['GEMINI_API_KEY']) !== '') {
            return trim($_ENV['GEMINI_API_KEY']);
        }

        if (isset($_SERVER['GEMINI_API_KEY']) && is_string($_SERVER['GEMINI_API_KEY']) && trim($_SERVER['GEMINI_API_KEY']) !== '') {
            return trim($_SERVER['GEMINI_API_KEY']);
        }

        return '';
    }

    private function getGeminiModel(): string
    {
        $value = getenv('GEMINI_MODEL');
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if (isset($_ENV['GEMINI_MODEL']) && is_string($_ENV['GEMINI_MODEL']) && trim($_ENV['GEMINI_MODEL']) !== '') {
            return trim($_ENV['GEMINI_MODEL']);
        }

        return 'gemini-1.5-flash';
    }
}
