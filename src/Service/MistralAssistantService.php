<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MistralAssistantService
{
    private const API_URL = 'https://api.mistral.ai/v1/chat/completions';

    private const SYSTEM_PROMPT = <<<'PROMPT'
Tu es un assistant e-sport expert, utile pour discuter des jeux compétitifs, des joueurs connus, des scènes pro et des bonnes pratiques de modération.
Tu réponds en français, de façon claire, utile et conversationnelle.
Tu peux parler librement de jeux e-sport populaires, joueurs populaires, compétitions, métas et performance.
Quand la demande concerne une sanction, propose un niveau adapté parmi: "ban from this match", "ban from this game", "ban from this tournament".
Justifie brièvement le choix selon la gravité, la répétition et l'impact sur l'intégrité compétitive.
N'invente pas des faits précis non vérifiables; si incertain, indique-le simplement.
PROMPT;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(default::MISTRAL_API_KEY)%')]
        private readonly string $apiKey,
    ) {
    }

    /**
     * @return array{success: bool, answer?: string, error?: string, status: int}
     */
    public function ask(string $message): array
    {
        return $this->askWithPrompt($message, self::SYSTEM_PROMPT);
    }

    /**
     * @return array{success: bool, answer?: string, error?: string, status: int}
     */
    public function askWithPrompt(string $message, string $systemPrompt): array
    {
        $content = trim($message);

        if ($content === '') {
            return [
                'success' => false,
                'error' => 'Message vide.',
                'status' => 400,
            ];
        }

        if ($this->apiKey === '') {
            return [
                'success' => false,
                'error' => 'MISTRAL_API_KEY non configurée.',
                'status' => 500,
            ];
        }

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'mistral-small-latest',
                    'temperature' => 0.3,
                    'max_tokens' => 180,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => $content,
                        ],
                    ],
                ],
                'timeout' => 25,
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($statusCode >= 400) {
                return [
                    'success' => false,
                    'error' => (string) ($data['error']['message'] ?? 'Erreur Mistral API.'),
                    'status' => 502,
                ];
            }

            $answer = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

            if ($answer === '') {
                return [
                    'success' => false,
                    'error' => 'Réponse IA vide.',
                    'status' => 502,
                ];
            }

            return [
                'success' => true,
                'answer' => $answer,
                'status' => 200,
            ];
        } catch (\Throwable) {
            return [
                'success' => false,
                'error' => 'Impossible de contacter l\'assistant vocal.',
                'status' => 502,
            ];
        }
    }
}
