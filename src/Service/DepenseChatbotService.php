<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\ChatbotConversation;
use App\Entity\Player;
use App\Repository\BudgetRepository;
use App\Repository\ChatbotConversationRepository;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\SecurityBundle\Security;

class DepenseChatbotService
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private DepenseRepository $depenseRepository,
        private AuthorizationService $authorizationService,
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager,
        private ChatbotConversationRepository $conversationRepository,
        private Security $security
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getFinancialSnapshot(): array
    {
        $allBudgets = $this->budgetRepository->findAll();
        $budgets = [];
        $expenses = [];

        foreach ($allBudgets as $budget) {
            if (!$budget instanceof Budget) {
                continue;
            }

            // Try to authorize based on user's teams
            try {
                if (!$this->authorizationService->canManageTeam($budget->getTeam())) {
                    continue;
                }
            } catch (\Exception $e) {
                error_log('[DepenseChatbot] Authorization check failed: ' . $e->getMessage());
                // If auth check fails (e.g., no user context), skip this budget
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

                $team = $budget->getTeam();
                $expenses[] = [
                    'id' => $depense->getId(),
                    'teamName' => $team ? $team->getName() : 'N/A',
                    'description' => $depense->getDescription(),
                    'montant' => round($amount, 2),
                    'categorie' => $category,
                    'date' => $depense->getDateCreation()?->format('Y-m-d'),
                ];
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

        // Sort expenses descending
        usort($expenses, fn($a, $b) => strtotime($b['date'] ?? '1970-01-01') <=> strtotime($a['date'] ?? '1970-01-01'));

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
            'expenses' => array_slice($expenses, 0, 50),
            'global' => [
                'budgetTotal' => round($globalTotal, 2),
                'spent' => round($globalSpent, 2),
                'remaining' => round($globalRemaining, 2),
            ],
        ];
    }

    public function ask(string $question, ?Player $user = null): string
    {
        error_log('[DepenseChatbot] ask() called with: ' . substr($question, 0, 100));
        
        if (trim($question) === '') {
            error_log('[DepenseChatbot] Question is empty!');
            return "❌ Veuillez poser une question valide.";
        }
        
        $snapshot = $this->getFinancialSnapshot();
        error_log('[DepenseChatbot] Snapshot ready: ' . count($snapshot['expenses']) . ' expenses');

        // Try Gemini API first
        error_log('[DepenseChatbot] Attempting Gemini API...');
        $geminiAnswer = $this->askGemini($question, $snapshot);
        if ($geminiAnswer !== null && trim($geminiAnswer) !== '') {
            error_log('[DepenseChatbot] Gemini returned answer, using it');
            $this->saveConversation($question, $geminiAnswer, $user);
            return $geminiAnswer;
        }

        // Fall back to intelligent local response
        error_log('[DepenseChatbot] Gemini failed or empty, using intelligent fallback');
        $fallbackAnswer = $this->buildLocalFallbackAnswer($question, $snapshot);
        
        if (trim($fallbackAnswer) === '') {
            error_log('[DepenseChatbot] Fallback returned empty! Returning default.');
            $fallbackAnswer = "📋 **Aperçu du budget:**\n\nBudget total: " . number_format($snapshot['global']['budgetTotal'], 2, '.', ' ') . " TND\nDépensé: " . number_format($snapshot['global']['spent'], 2, '.', ' ') . " TND\nRestant: " . number_format($snapshot['global']['remaining'], 2, '.', ' ') . " TND";
        }
        
        $this->saveConversation($question, $fallbackAnswer, $user);
        return $fallbackAnswer;
    }

    private function saveConversation(string $question, string $response, ?Player $user): void
    {
        try {
            $conversation = new ChatbotConversation();
            $conversation->setUserMessage($question);
            $conversation->setAiResponse($response);
            $conversation->setUser($user);

            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
        } catch (\Exception) {
            // Silently fail
        }
    }

    public function getConversationHistory(?Player $user = null, int $limit = 20): array
    {
        if (!$user) {
            return [];
        }

        try {
            return $this->conversationRepository->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC'],
                $limit
            );
        } catch (\Exception) {
            return [];
        }
    }

    private function askGemini(string $question, array $snapshot): ?string
    {
        $apiKey = $this->getEnvValue('GEMINI_API_KEY');
        if ($apiKey === '') {
            error_log('[DepenseChatbot] No Gemini API key configured');
            return null;
        }

        $model = $this->getEnvValue('GEMINI_MODEL', 'gemini-2.0-flash');
        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $model,
            rawurlencode($apiKey)
        );

        $systemPrompt = "Tu es un assistant financier intelligent pour managers e-sport. Réponds en français.\n"
            . "Tu dois:\n"
            . "1) Analyser et expliquer les budgets, dépenses, tendances\n"
            . "2) Suggest répartitions optimales par catégories\n"
            . "3) Alerter en cas de dépassement\n"
            . "4) Proposer des optimisations budgétaires\n"
            . "5) Donner des conseils stratégiques\n"
            . "Utilise STRICTEMENT les données fournies.\n"
            . "Montants en TND.\n"
            . "Question: {$question}\n"
            . "Données: " . json_encode($snapshot, JSON_UNESCAPED_UNICODE);

        try {
            error_log('[DepenseChatbot] Calling Gemini API');
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
                        'maxOutputTokens' => 800,
                    ],
                ],
                'timeout' => 15,
            ]);

            if ($response->getStatusCode() >= 400) {
                error_log('[DepenseChatbot] Gemini returned status ' . $response->getStatusCode());
                return null;
            }

            $data = $response->toArray(false);
            $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!is_string($answer) || trim($answer) === '') {
                error_log('[DepenseChatbot] Gemini returned empty answer');
                return null;
            }

            error_log('[DepenseChatbot] Gemini success');
            return trim($answer);
        } catch (\Exception $e) {
            error_log('[DepenseChatbot] Gemini error: ' . $e->getMessage());
            return null;
        }
    }

    private function buildLocalFallbackAnswer(string $question, array $snapshot): string
    {
        $questionLower = mb_strtolower($question);
        $total = (float) ($snapshot['global']['budgetTotal'] ?? 0.0);
        $spent = (float) ($snapshot['global']['spent'] ?? 0.0);
        $remaining = (float) ($snapshot['global']['remaining'] ?? 0.0);

        // Pattern: dernière dépenses
        if (str_contains($questionLower, 'dernière') || str_contains($questionLower, 'récent')) {
            $response = "📅 **Dernières dépenses validées :**\n\n";
            $recent = array_slice($snapshot['expenses'], 0, 10);
            if (!empty($recent)) {
                foreach ($recent as $expense) {
                    $response .= "• **{$expense['teamName']}** - {$expense['description']}\n";
                    $response .= "  Montant: " . number_format($expense['montant'], 2, '.', ' ') . " TND\n";
                    $response .= "  Catégorie: {$expense['categorie']} | Date: {$expense['date']}\n\n";
                }
            }
            return $response;
        }

        // Pattern: comparaison équipes
        if (str_contains($questionLower, 'compare') || str_contains($questionLower, 'équipe')) {
            $response = "📊 **Comparaison des budgets par équipe :**\n\n";
            foreach ($snapshot['budgets'] as $budget) {
                $util = $budget['budgetTotal'] > 0 ? round(($budget['spent'] / $budget['budgetTotal']) * 100, 1) : 0;
                $status = $util >= 100 ? '🚨' : ($util >= 90 ? '⚠️' : '✅');
                $response .= "{$status} **{$budget['teamName']}**\n";
                $response .= "  Budget: " . number_format($budget['budgetTotal'], 2, '.', ' ') . " TND\n";
                $response .= "  Utilisé: " . number_format($budget['spent'], 2, '.', ' ') . " TND ({$util}%)\n";
                $response .= "  Restant: " . number_format($budget['remaining'], 2, '.', ' ') . " TND\n\n";
            }
            return $response;
        }

        // Pattern: catégories
        if (str_contains($questionLower, 'catégorie') || str_contains($questionLower, 'category')) {
            $response = "📈 **Analyse par catégorie :**\n\n";
            $cats = ['materiel' => 0, 'transport' => 0, 'logistique' => 0, 'nourriture' => 0, 'autre' => 0];
            foreach ($snapshot['expenses'] as $exp) {
                if (isset($cats[$exp['categorie']])) {
                    $cats[$exp['categorie']] += $exp['montant'];
                }
            }
            arsort($cats);
            foreach ($cats as $c => $amt) {
                if ($amt > 0) {
                    $pct = $spent > 0 ? round(($amt / $spent) * 100, 1) : 0;
                    $response .= "• **{$c}**: " . number_format($amt, 2, '.', ' ') . " TND ({$pct}%)\n";
                }
            }
            return $response;
        }

        // Pattern: alerte/risque/problème
        if (str_contains($questionLower, 'alerte') || str_contains($questionLower, 'risque') ||
            str_contains($questionLower, 'danger') || str_contains($questionLower, 'problème') ||
            str_contains($questionLower, 'dépassement')) {
            
            $response = "🚨 **Analyse des risques :**\n\n";
            $critical = [];
            foreach ($snapshot['budgets'] as $b) {
                $u = $b['budgetTotal'] > 0 ? round(($b['spent'] / $b['budgetTotal']) * 100, 1) : 0;
                if ($u >= 90) {
                    $critical[] = ['name' => $b['teamName'], 'util' => $u, 'remaining' => $b['remaining']];
                }
            }
            if (empty($critical)) {
                $response .= "✅ Aucune alerte. Budget global à " . round(($spent / $total) * 100, 1) . "%\n";
            } else {
                foreach ($critical as $t) {
                    $status = $t['util'] >= 100 ? '🚨 CRITIQUE' : '⚠️ ALERTE';
                    $response .= "{$status}: {$t['name']} ({$t['util']}%)\n";
                }
            }
            return $response;
        }

        // Pattern: coûteux/cher/gaspillage
        if (str_contains($questionLower, 'cher') || str_contains($questionLower, 'coûteux') ||
            str_contains($questionLower, 'trop') || str_contains($questionLower, 'gaspill') ||
            str_contains($questionLower, 'énorme')) {
            
            $response = "💸 **Dépenses les plus élevées :**\n\n";
            if (empty($snapshot['expenses'])) {
                return $response . "Aucune dépense.";
            }
            $sorted = $snapshot['expenses'];
            usort($sorted, fn($a, $b) => $b['montant'] <=> $a['montant']);
            foreach (array_slice($sorted, 0, 5) as $e) {
                $response .= "• {$e['description']} ({$e['teamName']}): " . number_format($e['montant'], 2, '.', ' ') . " TND\n";
            }
            return $response;
        }

        // Pattern: stratégie/optimisation
        if (str_contains($questionLower, 'stratégie') || str_contains($questionLower, 'optimisation') ||
            str_contains($questionLower, 'strategy') || str_contains($questionLower, 'optimize') ||
            str_contains($questionLower, 'améliorer') || str_contains($questionLower, 'réduire') ||
            str_contains($questionLower, 'économie') || str_contains($questionLower, 'conseil')) {
            
            $response = "💡 **Stratégie d'optimisation budgétaire :**\n\n";
            $response .= "📊 **Situation actuelle :**\n";
            $utilGlobal = $total > 0 ? round(($spent / $total) * 100, 1) : 0;
            $response .= "• Budget global: " . number_format($total, 2, '.', ' ') . " TND ({$utilGlobal}% utilisé)\n";
            $response .= "• Restant: " . number_format($remaining, 2, '.', ' ') . " TND\n\n";

            // Catégories breakdown
            $response .= "**Dépenses par catégorie :**\n";
            $cats = ['materiel' => 0, 'transport' => 0, 'logistique' => 0, 'nourriture' => 0, 'autre' => 0];
            foreach ($snapshot['expenses'] as $e) {
                if (isset($cats[$e['categorie']])) {
                    $cats[$e['categorie']] += $e['montant'];
                }
            }
            arsort($cats);
            foreach ($cats as $c => $a) {
                if ($a > 0) {
                    $p = round(($a / $spent) * 100, 1);
                    $response .= "• {$c}: " . number_format($a, 2, '.', ' ') . " TND ({$p}%)\n";
                }
            }

            // Recommendations
            $response .= "\n**Recommandations :**\n";
            if ($spent >= $total) {
                $response .= "🚨 Dépassement détecté! Urgence absolue.\n";
                $excess = $spent - $total;
                $response .= "• Montant du dépassement: " . number_format($excess, 2, '.', ' ') . " TND\n";
                $response .= "• Action: Arrêter les dépenses non-essentielles\n";
            } else {
                $response .= "✅ Budget sous contrôle.\n";
                if ($remaining > 0) {
                    $mat = round($remaining * 0.33, 2);
                    $trans = round($remaining * 0.23, 2);
                    $log = round($remaining * 0.27, 2);
                    $food = round($remaining - $mat - $trans - $log, 2);
                    $response .= "• Répartition optimale du reste:\n";
                    $response .= "  - Matériel (33%): " . number_format($mat, 2, '.', ' ') . " TND\n";
                    $response .= "  - Transport (23%): " . number_format($trans, 2, '.', ' ') . " TND\n";
                    $response .= "  - Logistique (27%): " . number_format($log, 2, '.', ' ') . " TND\n";
                    $response .= "  - Nourriture (17%): " . number_format($food, 2, '.', ' ') . " TND\n";
                }
            }

            return $response;
        }

        // Default: Budget summary
        $response = "📋 **Résumé budgétaire global :**\n\n";
        $response .= "💰 Budget total: " . number_format($total, 2, '.', ' ') . " TND\n";
        $response .= "💸 Total dépensé: " . number_format($spent, 2, '.', ' ') . " TND\n";
        $response .= "💵 Budget restant: " . number_format($remaining, 2, '.', ' ') . " TND\n";
        
        if ($remaining > 0) {
            $mat = round($remaining * 0.33, 2);
            $trans = round($remaining * 0.23, 2);
            $log = round($remaining * 0.27, 2);
            $food = round($remaining - $mat - $trans - $log, 2);
            
            $response .= "\n**Répartition suggérée du budget restant:**\n";
            $response .= "• Matériel: " . number_format($mat, 2, '.', ' ') . " TND (33%)\n";
            $response .= "• Transport: " . number_format($trans, 2, '.', ' ') . " TND (23%)\n";
            $response .= "• Logistique: " . number_format($log, 2, '.', ' ') . " TND (27%)\n";
            $response .= "• Nourriture: " . number_format($food, 2, '.', ' ') . " TND (17%)\n";
        }
        
        return $response;
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
