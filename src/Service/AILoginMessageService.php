<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Player;
use App\Repository\GameRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service pour générer des messages de login personnalisés avec l'IA Mistral
 * 
 * Crée des messages de bienvenue dynamiques basés sur:
 * - L'historique de victoires/défaites du joueur
 * - Les séries en cours
 * - Les statistiques de performance
 * - La dernière connexion
 * 
 * @author Dev Team
 */
class AILoginMessageService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.mistral.ai/v1/chat/completions';
    private HttpClientInterface $httpClient;
    private GameRepository $gameRepository;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        GameRepository $gameRepository
    ) {
        $this->apiKey = $params->get('app.mistral_api_key');
        $this->httpClient = $httpClient;
        $this->gameRepository = $gameRepository;
    }

    /**
     * Générer un message de login personnalisé pour un joueur
     * 
     * @param User $user L'utilisateur qui se connecte
     * @return string Le message de bienvenue personnalisé
     */
    public function generateLoginMessage(User $user): string
    {
        // Ne traiter que les joueurs (Player)
        if (!$user instanceof Player) {
            return $this->getDefaultMessage($user);
        }

        // Récupérer les statistiques du joueur
        $stats = $this->getPlayerStats($user);
        
        // Construire le prompt pour Mistral
        $prompt = $this->buildMessagePrompt($user, $stats);

        try {
            // Appeler Mistral AI
            if (!$this->apiKey || strpos($this->apiKey, 'your_') !== false) {
                // Fallback si pas de clé API
                return $this->generateFallbackMessage($user, $stats);
            }

            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
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
                    'max_tokens' => 150,
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                return $this->generateFallbackMessage($user, $stats);
            }

            $content = $response->toArray();
            $message = $content['choices'][0]['message']['content'] ?? null;

            if ($message) {
                return trim($message);
            }

            return $this->generateFallbackMessage($user, $stats);

        } catch (\Throwable $e) {
            return $this->generateFallbackMessage($user, $stats);
        }
    }

    /**
     * Récupérer les statistiques du joueur
     */
    private function getPlayerStats(Player $player): array
    {
        // Ces valeurs devraient venir de votre système de statistiques
        // Pour l'instant, on utilise des valeurs par défaut
        
        return [
            'username' => $player->getUsername(),
            'firstName' => $player->getFirstName() ?? 'Joueur',
            'lastLogin' => $player->getUpdatedAt() ? $player->getUpdatedAt()->format('Y-m-d') : 'jamais',
            'wins' => random_int(5, 100), // À remplacer par vrai stats
            'losses' => random_int(2, 50),
            'winStreak' => random_int(0, 10),
            'rank' => ['Argent', 'Or', 'Platine', 'Diamant'][array_rand(['Argent', 'Or', 'Platine', 'Diamant'])],
        ];
    }

    /**
     * Construire le prompt pour Mistral
     */
    private function buildMessagePrompt(User $user, array $stats): string
    {
        $firstName = $stats['firstName'];
        $winStreak = $stats['winStreak'];
        $wins = $stats['wins'];
        $rank = $stats['rank'];
        $daysAgo = $this->calculateDaysSinceLastLogin($stats['lastLogin']);

        return <<<PROMPT
Tu es un assistant IA sympathique pour un jeu esports/gaming. 
Génère un message de bienvenue personnalisé et motivant pour un joueur qui se connecte.

**Contexte du joueur:**
- Prénom: {$firstName}
- Rang actuel: {$rank}
- Victoires totales: {$wins}
- Série de victoires: {$winStreak}
- Dernière connexion: il y a {$daysAgo} jours

**Instructions:**
1. Sois enthousiaste et motivant
2. Référence DIRECTEMENT ses statistiques (rang, série, nombre de victoires)
3. Si série > 5: félicite-le pour sa série 🔥
4. Si daysAgo > 7: sois heureux de le revoir après longtemps
5. Si daysAgo = 0: dis "Bienvenue de retour aujourd'hui!"
6. Ajoute des émojis pertinents (🎮🏆🔥📈)
7. Sois bref (1-2 phrases max)
8. Langue: Français

Exemple de bon message:
"Bienvenue {$firstName}! Tu es sur une série de 7 victoires 🔥 Ton rang {$rank} n'a qu'à bien se tenir!"

Retourne UNIQUEMENT le message, sans guillemets ni introduction.
PROMPT;
    }

    /**
     * Générer un message fallback (sans API)
     */
    private function generateFallbackMessage(User $user, array $stats): string
    {
        $firstName = $stats['firstName'];
        $winStreak = $stats['winStreak'];
        $wins = $stats['wins'];
        $rank = $stats['rank'];
        $daysAgo = $this->calculateDaysSinceLastLogin($stats['lastLogin']);

        $messages = [];

        // Messages basés sur la série
        if ($winStreak >= 8) {
            $messages[] = "🔥 $firstName! Tu déchires avec $winStreak victoires de suite!";
            $messages[] = "$firstName, tu es une machine! $winStreak wins d'affilée 🏆";
        } elseif ($winStreak >= 5) {
            $messages[] = "Tu es chaud $firstName! $winStreak victoires en cours 🔥";
            $messages[] = "$firstName, c'est fou! Ta série: $winStreak victoires! Continues 💪";
        } elseif ($winStreak >= 2) {
            $messages[] = "C'est parti $firstName! Tu accumules les victoires ($winStreak) 💯";
            $messages[] = "Bien joué $firstName! $winStreak wins de suite 📈";
        }

        // Messages basés sur le temps écoulé depuis la dernière connexion
        if ($daysAgo > 30) {
            $messages[] = "Bienvenue de retour $firstName! On t'a manqué 👋";
            $messages[] = "Ça faisait longtemps $firstName! Prêt à reconquérir le ${rank}? 🎮";
        } elseif ($daysAgo > 7) {
            $messages[] = "Salut $firstName! Tu as manqué une semaine! Rattrape-toi 💪";
            $messages[] = "$firstName, bon retour! Les ${rank}s t'attendent 🏆";
        }

        // Message générique de bienvenue
        if (empty($messages)) {
            $messages[] = "Bienvenue $firstName! Ready to play? 🎮";
            $messages[] = "C'est parti $firstName! Montons en rang 📈";
            $messages[] = "$firstName! Tu manquais au jeu 🎯";
            $messages[] = "Yo $firstName! Sur le $rank? Allons-y! 🚀";
        }

        return $messages[array_rand($messages)];
    }

    /**
     * Message complètement par défaut (non-joueur)
     */
    private function getDefaultMessage(User $user): string
    {
        $firstName = $user->getFirstName() ?? $user->getUsername();
        return "Bienvenue $firstName! 🎮";
    }

    /**
     * Calculer les jours depuis la dernière connexion
     */
    private function calculateDaysSinceLastLogin(string $lastLogin): int
    {
        try {
            $date = \DateTime::createFromFormat('Y-m-d', $lastLogin);
            if (!$date) {
                return 999; // Jamais connecté
            }
            $today = new \DateTime();
            $interval = $today->diff($date);
            return (int) $interval->days;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
