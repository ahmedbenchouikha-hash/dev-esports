<?php

namespace App\Service;

use App\Entity\TournamentRegistration;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiRegistrationReviewService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $params
    ) {}

    public function review(TournamentRegistration $registration): array
    {
        $apiKey = trim((string) $this->params->get('gemini_api_key'));
        $model = trim((string) $this->params->get('gemini_model'));
        if ($model === '') {
            $model = 'gemini-1.5-flash';
        }

        if ($apiKey === '') {
            $fallback = $this->buildLocalRecommendation($registration);
            $fallback['reason'] = 'Gemini not configured. ' . $fallback['reason'];

            return $fallback;
        }

        $tournament = $registration->getTournament();
        $team = $registration->getTeam();
        $player = $registration->getPlayer();

        $prompt = sprintf(
            "You are an esports registration reviewer.\n".
            "Evaluate whether this registration should be accepted.\n".
            "Return ONLY valid JSON with this exact schema: ".
            "{\"decision\":\"ACCEPTABLE|NOT_ACCEPTABLE|INSUFFICIENT_DATA\",\"reason\":\"short reason\",\"score\":0-100}.\n".
            "Registration data:\n".
            "- Team: %s\n".
            "- Team level: %s\n".
            "- Team game: %s\n".
            "- Team status: %s\n".
            "- Player: %s (%s)\n".
            "- Tournament: %s\n".
            "- Tournament status: %s\n".
            "- Tournament location: %s\n".
            "- Additional info: %s\n".
            "Scoring criteria: data completeness, consistency, and readiness.",
            $team?->getName() ?? 'Unknown',
            $team?->getNiveau() ?? 'Unknown',
            $team?->getJeu() ?? 'Unknown',
            $team?->getStatut() ?? 'Unknown',
            $player?->getNickname() ?? 'Unknown',
            $player?->getEmail() ?? 'Unknown',
            $tournament?->getName() ?? 'Unknown',
            $tournament?->getStatus() ?? 'Unknown',
            $tournament?->getLocation() ?? 'Unknown',
            $registration->getAdditionalInfo() ?: 'None'
        );

        try {
            $response = $this->httpClient->request(
                'POST',
                sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s', urlencode($model), urlencode($apiKey)),
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'maxOutputTokens' => 300,
                        ],
                    ],
                    'timeout' => 20,
                ]
            );

            $payload = $response->toArray(false);
            $text = $this->extractTextFromGeminiPayload($payload);
            if (!is_string($text) || trim($text) === '') {
                throw new \RuntimeException('Empty Gemini response');
            }

            $json = $this->extractJson($text);
            $parsed = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            $decision = strtoupper((string) ($parsed['decision'] ?? 'INSUFFICIENT_DATA'));
            if (!in_array($decision, ['ACCEPTABLE', 'NOT_ACCEPTABLE', 'INSUFFICIENT_DATA'], true)) {
                $decision = 'INSUFFICIENT_DATA';
            }

            $score = max(0, min(100, (int) ($parsed['score'] ?? 0)));
            $reason = trim((string) ($parsed['reason'] ?? 'No reason returned by model.'));

            return [
                'decision' => $decision,
                'reason' => $reason,
                'score' => $score,
                'source' => 'gemini',
            ];
        } catch (\Throwable) {
            $fallback = $this->buildLocalRecommendation($registration);
            $fallback['reason'] = 'Gemini unavailable. ' . $fallback['reason'];

            return $fallback;
        }
    }

    private function buildLocalRecommendation(TournamentRegistration $registration): array
    {
        $score = 50;
        $reasons = [];

        $team = $registration->getTeam();
        $tournament = $registration->getTournament();
        $player = $registration->getPlayer();

        if ($team && trim((string) $team->getName()) !== '') {
            $score += 10;
        } else {
            $score -= 20;
            $reasons[] = 'Team information is incomplete.';
        }

        if ($player && trim((string) $player->getEmail()) !== '') {
            $score += 10;
        } else {
            $score -= 20;
            $reasons[] = 'Player contact is missing.';
        }

        if (trim((string) $registration->getContactEmail()) !== '') {
            $score += 10;
        } else {
            $score -= 10;
            $reasons[] = 'Registration contact email is missing.';
        }

        $additionalInfo = trim((string) $registration->getAdditionalInfo());
        if ($additionalInfo !== '') {
            $score += 10;
        } else {
            $reasons[] = 'No additional motivation/details provided.';
        }

        $tournamentStatus = strtolower((string) ($tournament?->getStatus() ?? ''));
        if ($tournamentStatus === 'pending') {
            $score += 10;
        } elseif (in_array($tournamentStatus, ['cancelled', 'completed'], true)) {
            $score -= 30;
            $reasons[] = 'Tournament is not open for standard registration review.';
        }

        $score = max(0, min(100, $score));
        $decision = $score >= 60 ? 'ACCEPTABLE' : 'NOT_ACCEPTABLE';

        if (count($reasons) === 0) {
            $reasons[] = 'Registration appears coherent and sufficiently complete.';
        }

        return [
            'decision' => $decision,
            'reason' => implode(' ', $reasons),
            'score' => $score,
            'source' => 'local_heuristic',
        ];
    }

    private function extractTextFromGeminiPayload(array $payload): string
    {
        $candidates = $payload['candidates'] ?? null;
        if (!is_array($candidates)) {
            return '';
        }

        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? null;
            if (!is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                $text = $part['text'] ?? null;
                if (is_string($text) && trim($text) !== '') {
                    return $text;
                }
            }
        }

        return '';
    }

    private function extractJson(string $text): string
    {
        $trimmed = trim($text);
        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```[a-zA-Z]*\s*/', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
        }

        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');
        if ($start === false || $end === false || $end <= $start) {
            throw new \RuntimeException('No JSON object found in Gemini output');
        }

        return substr($trimmed, $start, $end - $start + 1);
    }
}
