<?php

namespace App\Service;

use App\DTO\RewardAnalysisDTO;
use App\Entity\DemandeRecompense;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * AI-powered reward analysis service using Groq API
 * Analyzes reward requests for legitimacy, fraud detection, and suggestions
 */
class AIRewardAnalysisService
{
    private const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL = 'llama-3.3-70b-versatile';
    private const TEMPERATURE = 0.7;
    private const MAX_TOKENS = 1000;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $groqApiKey
    ) {
    }

    /**
     * Generate a suggestion for the reward request motif
     * 
     * @param DemandeRecompense $demande The reward request
     * @return string Generated motif suggestion
     */
    public function generateMotifSuggestion(DemandeRecompense $demande): string
    {
        try {
            $recompense = $demande->getRecompense();
            if (!$recompense) {
                return 'Reward request submitted.';
            }

            $requesterName = $demande->getNomDemandeur();
            $rewardName = $recompense->getRecompense();
            $rewardType = $recompense->getType();

            $prompt = <<<PROMPT
Generate a brief, professional, and compelling French reward request motif (reason) for the following:

Requester Name: {$requesterName}
Reward: {$rewardName}
Reward Type: {$rewardType}

The motif should be:
- Between 50-150 characters
- Professional and clear
- Explain why this reward is deserved
- Be specific and genuine

Respond with ONLY the motif text, nothing else.
PROMPT;

            $request = $this->buildGroqRequest($prompt, 200);
            $response = $this->sendGroqRequest($request);
            
            if ($response['success'] && isset($response['content'])) {
                $suggestion = trim($response['content']);
                
                // Ensure it meets minimum length requirement
                if (strlen($suggestion) >= 50) {
                    $this->logger->info('AI motif suggestion generated successfully', [
                        'length' => strlen($suggestion),
                        'requester' => $requesterName,
                    ]);
                    return $suggestion;
                }
            }

            // Fallback if AI doesn't generate valid suggestion
            return "I kindly request the {$rewardName} as recognition for my participation and achievements.";

        } catch (\Exception $e) {
            $this->logger->error('Error generating motif suggestion', [
                'error' => $e->getMessage(),
            ]);
            
            // Safe fallback
            $rewardName = $demande->getRecompense()?->getRecompense() ?? 'reward';
            return "I am requesting the {$rewardName} reward.";
        }
    }

    /**
     * Analyze a reward request for legitimacy, fraud detection, and AI insights
     * 
     * @param DemandeRecompense $demande The reward request to analyze
     * @return RewardAnalysisDTO Analysis results
     */
    public function analyzeDemand(DemandeRecompense $demande): RewardAnalysisDTO
    {
        $dto = new RewardAnalysisDTO();
        
        try {
            $recompense = $demande->getRecompense();
            $requesterName = $demande->getNomDemandeur();
            $email = $demande->getEmail();
            $motif = $demande->getMotif();

            if (!$recompense) {
                $this->logger->warning('Attempting to analyze demand without reward reference');
                return $this->buildDefaultAnalysis($dto);
            }

            $prompt = $this->buildAnalysisPrompt($requesterName, $email, $motif, $recompense);
            $request = $this->buildGroqRequest($prompt, 1000);
            $response = $this->sendGroqRequest($request);

            if ($response['success'] && isset($response['content'])) {
                $analysis = $this->parseAnalysisResponse($response['content']);
                $dto = $this->hydrateDTOFromAnalysis($dto, $analysis);
            } else {
                $this->logger->warning('AI analysis failed, using default values');
                $dto = $this->buildDefaultAnalysis($dto);
            }

            $dto->setAnalyzedAt(new \DateTime());
            
            return $dto;

        } catch (\Exception $e) {
            $this->logger->error('Error analyzing reward demand', [
                'error' => $e->getMessage(),
            ]);
            
            $dto->setAnalyzedAt(new \DateTime());
            return $this->buildDefaultAnalysis($dto);
        }
    }

    /**
     * Build the analysis prompt for AI
     */
    private function buildAnalysisPrompt(string $requesterName, string $email, string $motif, $recompense): string
    {
        $rewardName = $recompense->getRecompense();
        $rewardType = $recompense->getType();
        $classement = $recompense->getClassement();

        return <<<PROMPT
Analyze this reward request and provide a JSON response with the following fields:
- legitimacy_score (0-100): How legitimate is this request?
- fraud_type (string or null): Type of fraud detected (if any), e.g., "fake_identity", "spam", "duplicate", "suspicious", null if none
- confidence_level (0-1): Confidence in your analysis
- key_points (array of strings): Main observations about the request
- sentiment (string): Overall sentiment - "positive", "negative", "neutral"
- suggested_action (string): "auto_approve", "review", "reject"
- analysis_reason (string): Brief explanation of the analysis
- should_auto_approve (boolean): Whether this should be auto-approved

Reward Request Details:
- Requester Name: {$requesterName}
- Email: {$email}
- Reward: {$rewardName} (Rank #{$classement}, Type: {$rewardType})
- Request Reason: {$motif}

Evaluation Criteria:
1. Email validity (check for suspicious patterns)
2. Name credibility (check for spam patterns)
3. Motif quality (professional and specific vs generic/copy-paste)
4. Reward tier appropriateness
5. Overall legitimacy indicators

Respond ONLY with valid JSON, no additional text.
PROMPT;
    }

    /**
     * Build a Groq API request
     */
    private function buildGroqRequest(string $prompt, int $maxTokens): array
    {
        return [
            'method' => 'POST',
            'url' => self::GROQ_URL,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->groqApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'model' => self::MODEL,
                'temperature' => self::TEMPERATURE,
                'max_tokens' => $maxTokens,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert fraud detection and reward validation AI. Analyze requests carefully and provide structured analysis.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ],
        ];
    }

    /**
     * Send request to Groq API
     */
    private function sendGroqRequest(array $requestConfig): array
    {
        try {
            if (empty($this->groqApiKey)) {
                $this->logger->warning('Groq API key not configured');
                return [
                    'success' => false,
                    'error' => 'API key not configured',
                ];
            }

            $response = $this->httpClient->request(
                $requestConfig['method'],
                $requestConfig['url'],
                [
                    'headers' => $requestConfig['headers'],
                    'json' => $requestConfig['json'],
                    'timeout' => 30,
                ]
            );

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                $data = $response->toArray();
                
                if (isset($data['choices'][0]['message']['content'])) {
                    return [
                        'success' => true,
                        'content' => $data['choices'][0]['message']['content'],
                    ];
                }
            }

            $this->logger->error('Groq API error', [
                'status' => $statusCode,
                'response' => $response->getContent(false),
            ]);

            return [
                'success' => false,
                'error' => "API returned status {$statusCode}",
            ];

        } catch (\Exception $e) {
            $this->logger->error('Groq API request failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse the analysis response from AI
     */
    private function parseAnalysisResponse(string $responseContent): array
    {
        try {
            // Try to extract JSON from response
            $json = json_decode($responseContent, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return $json;
            }

            // If direct parsing fails, try to extract JSON from the content
            if (preg_match('/\{.*\}/s', $responseContent, $matches)) {
                $json = json_decode($matches[0], true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    return $json;
                }
            }

            $this->logger->warning('Could not parse analysis response as JSON', [
                'response' => substr($responseContent, 0, 200),
            ]);

            return [];

        } catch (\Exception $e) {
            $this->logger->error('Error parsing analysis response', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Hydrate DTO from analysis data
     */
    private function hydrateDTOFromAnalysis(RewardAnalysisDTO $dto, array $analysis): RewardAnalysisDTO
    {
        if (isset($analysis['legitimacy_score'])) {
            $dto->setLegitimacyScore((int) $analysis['legitimacy_score']);
        }

        if (isset($analysis['fraud_type'])) {
            $dto->setFraudType($analysis['fraud_type']);
        }

        if (isset($analysis['confidence_level'])) {
            $dto->setConfidenceLevel((float) $analysis['confidence_level']);
        }

        if (isset($analysis['key_points']) && is_array($analysis['key_points'])) {
            $dto->setKeyPoints($analysis['key_points']);
        }

        if (isset($analysis['sentiment'])) {
            $dto->setSentiment($analysis['sentiment']);
        }

        if (isset($analysis['suggested_reward_type'])) {
            $dto->setSuggestedRewardType($analysis['suggested_reward_type']);
        }

        if (isset($analysis['analysis_reason'])) {
            $dto->setAnalysisReason($analysis['analysis_reason']);
        }

        if (isset($analysis['should_auto_approve'])) {
            $dto->setShouldAutoApprove((bool) $analysis['should_auto_approve']);
        }

        return $dto;
    }

    /**
     * Build default analysis when AI fails
     */
    private function buildDefaultAnalysis(RewardAnalysisDTO $dto): RewardAnalysisDTO
    {
        return $dto
            ->setLegitimacyScore(65)
            ->setFraudType(null)
            ->setConfidenceLevel(0.5)
            ->setKeyPoints(['AI analysis unavailable', 'Manual review recommended'])
            ->setSentiment('neutral')
            ->setSuggestedRewardType(null)
            ->setAnalysisReason('Default analysis: AI service unavailable')
            ->setShouldAutoApprove(false);
    }
}
