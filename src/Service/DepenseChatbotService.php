<?php

namespace App\Service;

use App\Entity\Budget;
use App\Repository\BudgetRepository;
use App\Repository\DepenseRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DepenseChatbotService
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private DepenseRepository $depenseRepository,
        private AuthorizationService $authorizationService,
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getFinancialSnapshot(): array
    {
        $allBudgets = $this->budgetRepository->findAll();
        $budgets = [];

        foreach ($allBudgets as $budget) {
            if (!$budget instanceof Budget) {
                continue;
            }

            if (!$this->authorizationService->canManageTeam($budget->getTeam())) {
                continue;
            }

            $depensesValidees = $this->depenseRepository->findBy([
                'team' => $budget->getTeam(),
                'statut' => 'validée',
            ]);

            $spent = 0.0;
            $byCategory = [
                'materiel' => 0.0,
                'transport' => 0.0,
                'logistique' => 0.0,
                'nourriture' => 0.0,
                'autre' => 0.0,
            ];

            foreach ($depensesValidees as $depense) {
                $amount = (float) $depense->getMontant();
                $spent += $amount;
                $category = (string) ($depense->getCategorie() ?? 'autre');
                if (!array_key_exists($category, $byCategory)) {
                    $category = 'autre';
                }
                $byCategory[$category] += $amount;
            }

            $total = (float) $budget->getMontantAlloue();
            $remaining = $total - $spent;

            $budgets[] = [
                'teamId' => $budget->getTeam()?->getId(),
                'teamName' => $budget->getTeam()?->getName(),
                'budgetTotal' => round($total, 2),
                'spent' => round($spent, 2),
                'remaining' => round($remaining, 2),
                'byCategory' => array_map(fn (float $value) => round($value, 2), $byCategory),
            ];
        }

        $globalTotal = 0.0;
        $globalSpent = 0.0;
        $globalRemaining = 0.0;

        foreach ($budgets as $item) {
            $globalTotal += (float) $item['budgetTotal'];
            $globalSpent += (float) $item['spent'];
            $globalRemaining += (float) $item['remaining'];
        }

        return [
            'budgets' => $budgets,
            'global' => [
                'budgetTotal' => round($globalTotal, 2),
                'spent' => round($globalSpent, 2),
                'remaining' => round($globalRemaining, 2),
            ],
        ];
    }

    public function ask(string $question): string
    {
        $snapshot = $this->getFinancialSnapshot();

        $geminiAnswer = $this->askGemini($question, $snapshot);
        if ($geminiAnswer !== null) {
            return $geminiAnswer;
        }

        return $this->buildLocalFallbackAnswer($question, $snapshot);
    }

    private function askGemini(string $question, array $snapshot): ?string
    {
        $apiKey = $this->getEnvValue('GEMINI_API_KEY');
        if ($apiKey === '') {
            return null;
        }

        $model = $this->getEnvValue('GEMINI_MODEL', 'gemini-1.5-flash');
        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($model),
            rawurlencode($apiKey)
        );

        $systemPrompt = "Tu es un assistant financier pour managers e-sport. Réponds en français, clair et court.\n"
            . "Tu dois aider à:\n"
            . "1) Calculer budget restant\n"
            . "2) Suggérer répartition automatique par catégories (matériel, transport, logistique, nourriture)\n"
            . "3) Donner alerte en cas de dépassement\n"
            . "4) Proposer optimisation simple\n"
            . "5) Donner résumé automatique\n"
            . "Utilise STRICTEMENT les données financières fournies.\n"
            . "Montants en TND.\n"
            . "Si l'utilisateur demande une répartition, propose des montants concrets qui totalisent le restant.\n"
            . "Si dépassement détecté, précise de combien.\n"
            . "Question manager: {$question}\n"
            . "Données: " . json_encode($snapshot, JSON_UNESCAPED_UNICODE);

        try {
            $response = $this->httpClient->request('POST', $endpoint, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => $systemPrompt,
                        ]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 600,
                    ],
                ],
                'timeout' => 15,
            ]);

            if ($response->getStatusCode() >= 400) {
                return null;
            }

            $data = $response->toArray(false);
            $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!is_string($answer) || trim($answer) === '') {
                return null;
            }

            return trim($answer);
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildLocalFallbackAnswer(string $question, array $snapshot): string
    {
        $total = (float) ($snapshot['global']['budgetTotal'] ?? 0.0);
        $spent = (float) ($snapshot['global']['spent'] ?? 0.0);
        $remaining = (float) ($snapshot['global']['remaining'] ?? 0.0);

        $material = round($remaining * 0.33, 2);
        $transport = round($remaining * 0.23, 2);
        $logistics = round($remaining * 0.27, 2);
        $food = round($remaining - $material - $transport - $logistics, 2);

        $questionLower = mb_strtolower($question);

        if (str_contains($questionLower, 'optim') || str_contains($questionLower, 'rédu') || str_contains($questionLower, 'reduce')) {
            return "Suggestion d'optimisation:\n"
                . "- Si vous réduisez transport de 200 TND, vous pouvez augmenter nourriture de 200 TND.\n"
                . "- Gardez matériel prioritaire si des achats techniques sont prévus.\n\n"
                . "Résumé: Budget total {$total} TND | Dépensé {$spent} TND | Restant {$remaining} TND.";
        }

        if (str_contains($questionLower, 'répartition') || str_contains($questionLower, 'repartition') || str_contains($questionLower, 'suggest')) {
            return "Pour {$remaining} TND restant, je recommande:\n"
                . "- Matériel: {$material} TND\n"
                . "- Transport: {$transport} TND\n"
                . "- Logistique: {$logistics} TND\n"
                . "- Nourriture: {$food} TND\n\n"
                . "Résumé: Budget total {$total} TND | Dépensé {$spent} TND | Restant {$remaining} TND.";
        }

        return "Résumé automatique:\n"
            . "- Budget total: {$total} TND\n"
            . "- Total dépense: {$spent} TND\n"
            . "- Reste: {$remaining} TND\n"
            . "- Répartition suggérée:\n"
            . "  • Matériel: {$material} TND\n"
            . "  • Transport: {$transport} TND\n"
            . "  • Logistique: {$logistics} TND\n"
            . "  • Nourriture: {$food} TND";
    }

    private function getEnvValue(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if (isset($_ENV[$key]) && is_string($_ENV[$key]) && trim($_ENV[$key]) !== '') {
            return trim($_ENV[$key]);
        }

        if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && trim($_SERVER[$key]) !== '') {
            return trim($_SERVER[$key]);
        }

        return $default;
    }
}
