<?php
/**
 * Test rapide de l'API Mistral pour vérifier le motif suggestion
 */

require 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv('.env.local');

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? null;

if (!$apiKey) {
    echo "❌ MISTRAL_API_KEY non trouvée dans .env.local\n";
    exit(1);
}

echo "✅ MISTRAL_API_KEY trouvée: " . substr($apiKey, 0, 15) . "...\n\n";

$httpClient = HttpClient::create();

$prompt = <<<PROMPT
Tu es un assistant pour une plateforme esports.
Rédige un motif de demande court (2-3 phrases) en français.

Contexte:
- Nom du demandeur: Jean Dupont
- Récompense: Trophy Argent
- Type: Excellence

Contraintes:
- Ton professionnel et positif
- 2-3 phrases maximum
- Pas de guillemets ni de liste
PROMPT;

echo "📤 Envoi de la requête à Mistral AI...\n";
echo "Modèle: mistral-small\n";
echo "Prompt: " . substr($prompt, 0, 50) . "...\n\n";

try {
    $response = $httpClient->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'mistral-small',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 200,
        ]
    ]);

    $statusCode = $response->getStatusCode();
    echo "📨 Status: $statusCode\n";

    $content = $response->toArray(false);
    
    // Afficher la réponse complète
    echo "\n📋 Réponse complète:\n";
    echo json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    if ($statusCode === 200 && isset($content['choices'][0]['message']['content'])) {
        $text = trim($content['choices'][0]['message']['content']);
        echo "✅ SUCCÈS! Motif généré:\n";
        echo "───────────────────────────────────────\n";
        echo $text . "\n";
        echo "───────────────────────────────────────\n";
    } else {
        echo "❌ ERREUR: Réponse inattendue\n";
        echo "Code: $statusCode\n";
    }

} catch (\Throwable $e) {
    echo "❌ ERREUR lors de l'appel API:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
