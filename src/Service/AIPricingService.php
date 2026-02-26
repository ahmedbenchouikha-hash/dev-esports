<?php

namespace App\Service;

use App\Entity\Ticket;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * AI-powered pricing service using Groq API
 * Free tier: 7,000 requests/month
 */
class AIPricingService
{
    private const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL = 'llama-3.3-70b-versatile';

    public function __construct(
        private HttpClientInterface $httpClient,
        private SmartPricingService $smartPricingService,
        private string $groqApiKey
    ) {
    }

    /**
     * Get AI-powered pricing recommendation using Groq API
     */
    public function getAIPricingRecommendation(Ticket $ticket): array
    {
        try {
            // Get the current rule-based recommendation as context
            $ruleBased = $this->smartPricingService->getPricingRecommendation($ticket);
            
            // Create a prompt for the AI
            $prompt = $this->buildPricingPrompt($ticket, $ruleBased);
            
            // Call Groq API
            $response = $this->callGroq($prompt);
            
            // Parse the AI response
            return $this->parsePricingResponse($response, $ticket, $ruleBased);
            
        } catch (\Exception $e) {
            // Fallback to rule-based if AI fails
            $ruleBased = $this->smartPricingService->getPricingRecommendation($ticket);
            
            // Store the error message for debugging
            $errorMsg = $e->getMessage();
            
            return [
                'price' => $ticket->getPrice(),
                'action' => 'keep',
                'reason' => 'AI service unavailable - using rule-based pricing',
                'confidence' => 'low',
                'original_price' => $ticket->getPrice(),
                'change_percentage' => 0,
                'ruleBased' => $ruleBased,
                'aiModelUsed' => self::MODEL,
                'timestamp' => new \DateTime(),
                'error' => $errorMsg,
                'debug_trace' => 'Exception in getAIPricingRecommendation'
            ];
        }
    }

    /**
     * Build a structured prompt for the AI
     */
    private function buildPricingPrompt(Ticket $ticket, array $ruleBased): string
    {
        $game = $ticket->getGame();
        $team1 = $game?->getTeam1();
        $team2 = $game?->getTeam2();
        $gameName = $game ? (string)$game : 'Unknown';
        $team1Name = $team1?->getName() ?? 'Team 1';
        $team2Name = $team2?->getName() ?? 'Team 2';
        
        $currentPrice = $ticket->getPrice();
        $recommendedPrice = $ruleBased['smartPrice'] ?? $currentPrice;
        $demandMultiplier = $ruleBased['demand'] ?? 1.0;
        $ticketType = $ticket->getType();
        $percentChange = $ruleBased['percentChange'] ?? 0;
        
        $prompt = "You are an expert pricing strategist for an esports ticket platform.\n\n";
        $prompt .= "Current Ticket Information:\n";
        $prompt .= "- Match: {$gameName}\n";
        $prompt .= "- Team 1: {$team1Name}\n";
        $prompt .= "- Team 2: {$team2Name}\n";
        $prompt .= "- Ticket Type: {$ticketType}\n";
        $prompt .= "- Current Price: €{$currentPrice}\n";
        $prompt .= "- Recommended Price (Algorithm): €{$recommendedPrice}\n";
        $prompt .= "- Suggested Change: {$percentChange}%\n";
        $prompt .= "- Demand Multiplier: {$demandMultiplier}x\n\n";
        $prompt .= "Based on market dynamics, provide your pricing recommendation.\n\n";
        $prompt .= "Respond with EXACTLY this format, nothing else:\n";
        $prompt .= "PRICE: [number only]\n";
        $prompt .= "ACTION: [increase/keep/decrease]\n";
        $prompt .= "REASON: [one sentence]\n";
        $prompt .= "CONFIDENCE: [high/medium/low]\n\n";
        $prompt .= "Example:\n";
        $prompt .= "PRICE: 52.50\n";
        $prompt .= "ACTION: increase\n";
        $prompt .= "REASON: High demand and limited availability warrant premium pricing\n";
        $prompt .= "CONFIDENCE: high\n\n";
        $prompt .= "Now provide your recommendation:";
        
        return $prompt;
    }

    /**
     * Call Groq API
     */
    private function callGroq(string $prompt): string
    {
        if (!$this->groqApiKey) {
            throw new \Exception('GROQ_API_KEY not configured in .env');
        }

        try {
            $requestBody = [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 150,
            ];

            $response = $this->httpClient->request('POST', self::GROQ_URL, [
                'json' => $requestBody,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();
            
            // Check for API errors first
            if (isset($data['error'])) {
                throw new \Exception('Groq API Error: ' . json_encode($data['error']));
            }
            
            if (isset($data['choices'][0]['message']['content'])) {
                return $data['choices'][0]['message']['content'];
            }
            
            throw new \Exception('Invalid Groq response format: ' . json_encode($data));
        } catch (\Exception $e) {
            $message = $e->getMessage();
            // If it's an HTTP error, try to extract more details
            if (strpos($message, 'HTTP/2') !== false) {
                $message .= ' - Possible causes: invalid API key, wrong endpoint, or request format issue';
            }
            throw new \Exception('Groq API call failed: ' . $message);
        }
    }

    /**
     * Parse the AI response and extract pricing
     */
    private function parsePricingResponse(string $response, Ticket $ticket, array $ruleBased): array
    {
        $lines = explode("\n", trim($response));
        $price = $ticket->getPrice();
        $action = 'keep';
        $reason = 'AI analysis complete';
        $confidence = 'medium';

        foreach ($lines as $line) {
            if (strpos($line, 'PRICE:') !== false) {
                $priceStr = trim(str_replace('PRICE:', '', $line));
                $extractedPrice = (float) filter_var($priceStr, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                if ($extractedPrice > 0) {
                    $price = $extractedPrice;
                }
            } elseif (strpos($line, 'ACTION:') !== false) {
                $action = strtolower(trim(str_replace('ACTION:', '', $line)));
            } elseif (strpos($line, 'REASON:') !== false) {
                $reason = trim(str_replace('REASON:', '', $line));
            } elseif (strpos($line, 'CONFIDENCE:') !== false) {
                $confidence = strtolower(trim(str_replace('CONFIDENCE:', '', $line)));
            }
        }

        return [
            'price' => $price,
            'action' => $action,
            'reason' => $reason,
            'confidence' => $confidence,
            'original_price' => $ticket->getPrice(),
            'change_percentage' => round((($price - $ticket->getPrice()) / $ticket->getPrice()) * 100, 2),
            'ruleBased' => $ruleBased,
            'aiModelUsed' => self::MODEL,
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * Check if Groq API is accessible
     */
    public function isGeminiAvailable(): bool
    {
        return !empty($this->groqApiKey);
    }

    /**
     * Debug: Get API key status (without exposing the key)
     */
    public function getDebugApiKeyStatus(): string
    {
        if (empty($this->groqApiKey)) {
            return 'NOT_LOADED';
        }
        return 'KEY_LENGTH:' . strlen($this->groqApiKey);
    }

    /**
     * Get available models (for UI)
     */
    public function getAvailableModels(): array
    {
        return [
            [
                'name' => 'mixtral-8x7b-32768',
                'size' => 'Cloud API'
            ]
        ];
    }

    /**
     * Debug method to test Groq API with detailed request/response info
     */
    public function debugGroqRequest(): array
    {
        $debugInfo = [
            'api_key_loaded' => !empty($this->groqApiKey),
            'api_key_length' => strlen($this->groqApiKey),
            'api_endpoint' => self::GROQ_URL,
            'model' => self::MODEL,
        ];

        if (empty($this->groqApiKey)) {
            return array_merge($debugInfo, [
                'error' => 'GROQ_API_KEY not configured in .env file',
                'status' => 'FAILED - No API key'
            ]);
        }

        try {
            // Prepare test request
            $testPrompt = 'What is 2+2?';
            $requestBody = [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $testPrompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 50,
            ];

            // Make the request
            $response = $this->httpClient->request('POST', self::GROQ_URL, [
                'json' => $requestBody,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray();

            return array_merge($debugInfo, [
                'request_sent' => [
                    'model' => $requestBody['model'],
                    'temperature' => $requestBody['temperature'],
                    'max_tokens' => $requestBody['max_tokens'],
                    'headers' => [
                        'Authorization' => 'Bearer [REDACTED]',
                        'Content-Type' => 'application/json',
                    ]
                ],
                'response_status' => $statusCode,
                'response_data' => $data,
                'status' => 'SUCCESS'
            ]);
        } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
            // Try to extract the error response from Groq
            $responseContent = '';
            try {
                $responseContent = $e->getResponse()->getContent(false);
            } catch (\Exception $innerEx) {
                $responseContent = 'Could not read response body';
            }
            
            return array_merge($debugInfo, [
                'error' => $e->getMessage(),
                'error_exception' => get_class($e),
                'groq_response_body' => $responseContent,
                'status' => 'FAILED - API Error',
                'common_issues' => [
                    'Model name wrong?' => 'Groq model should be: mixtral-8x7b-32768',
                    'Auth header format?' => 'Should be: Bearer <token>',
                    'JSON format?' => 'Messages must be array of {role, content}',
                    'Missing fields?' => 'Check all required fields are present'
                ]
            ]);
        } catch (\Exception $e) {
            return array_merge($debugInfo, [
                'error' => $e->getMessage(),
                'error_exception' => get_class($e),
                'status' => 'FAILED - Unexpected Error'
            ]);
        }
    }
}
