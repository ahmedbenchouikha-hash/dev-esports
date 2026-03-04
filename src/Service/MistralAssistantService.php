<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MistralAssistantService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    /**
     * @return array{status:int,success:bool,answer?:string,error?:string}
     */
    public function ask(string $message): array
    {
        return $this->askWithPrompt($message, '');
    }

    /**
     * @return array{status:int,success:bool,answer?:string,error?:string}
     */
    public function askWithPrompt(string $message, string $prompt): array
    {
        $clean = trim($message);

        if ($clean === '') {
            return [
                'status' => 400,
                'success' => false,
                'error' => 'Message vide.',
            ];
        }

        $apiKey = $this->getEnv('MISTRAL_API_KEY');
        if ($apiKey === '') {
            return [
                'status' => 200,
                'success' => true,
                'answer' => $this->buildModerationAnswer($clean),
            ];
        }

        $model = $this->getEnv('MISTRAL_MODEL') ?: 'mistral-small-latest';
        $fullPrompt = trim($prompt) === '' ? $clean : ($prompt . "\n\nUtilisateur: " . $clean);

        try {
            $response = $this->httpClient->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => 0.3,
                    'max_tokens' => 400,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un assistant e-sport utile, clair et concis. Réponds en français.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $fullPrompt,
                        ],
                    ],
                ],
                'timeout' => 15,
            ]);

            if ($response->getStatusCode() >= 400) {
                return [
                    'status' => $response->getStatusCode(),
                    'success' => false,
                    'error' => 'Erreur API Mistral.',
                ];
            }

            $data = $response->toArray(false);
            $answer = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

            if ($answer === '') {
                return [
                    'status' => 502,
                    'success' => false,
                    'error' => 'Réponse vide de Mistral.',
                ];
            }

            return [
                'status' => 200,
                'success' => true,
                'answer' => $answer,
            ];
        } catch (\Throwable) {
            return [
                'status' => 200,
                'success' => true,
                'answer' => $this->buildModerationAnswer($clean),
            ];
        }
    }

    private function getEnv(string $name): string
    {
        $value = getenv($name);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if (isset($_ENV[$name]) && is_string($_ENV[$name]) && trim($_ENV[$name]) !== '') {
            return trim($_ENV[$name]);
        }

        if (isset($_SERVER[$name]) && is_string($_SERVER[$name]) && trim($_SERVER[$name]) !== '') {
            return trim($_SERVER[$name]);
        }

        return '';
    }

    private function buildModerationAnswer(string $message): string
    {
        $lower = mb_strtolower($message);

        if (str_contains($lower, 'harc') || str_contains($lower, 'insulte') || str_contains($lower, 'toxic')) {
            return 'Cas sensible détecté: privilégier une sanction stricte et documentée, avec vérification des preuves.';
        }

        if (str_contains($lower, 'triche') || str_contains($lower, 'cheat')) {
            return 'Suspicion de triche: recommander une enquête rapide et une sanction proportionnée à l’impact compétitif.';
        }

        if (str_contains($lower, 'ban') || str_contains($lower, 'punition')) {
            return 'Pour une punition juste: vérifier la récidive, la gravité et l’impact sur le match/tournoi.';
        }

        return 'Analyse reçue. Donne plus de contexte (preuves, récidive, impact) pour une recommandation plus précise.';
    }
}
