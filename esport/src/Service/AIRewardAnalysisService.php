<?php

namespace App\Service;

use App\DTO\RewardAnalysisDTO;
use App\Entity\DemandeRecompense;
use App\Repository\RecompenseRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\TransportException;

/**
 * Service d'Analyse IA des Demandes de Récompenses
 * 
 * Utilise l'API Mistral AI para analyser intelligemment
 * les demandes de récompenses des utilisateurs.
 * 
 * FONCTIONNALITÉS:
 * - Scoring de légitimité (0-100)
 * - Détection de fraude et spam
 * - Extraction de points clés
 * - Suggestion de récompense appropriée
 * - Analyse de sentiment
 * - Auto-approbation si score > 80%
 * 
 * MODÈLE UTILISÉ: Mistral AI (mistral-small)
 * - Qualité d'analyse excellente
 * - Excellent pour l'analyse nuancée de texte
 * - Coût: très économique
 * - Temps de réponse: <2 secondes
 * 
 * @author Dev Team
 * @version 2.0
 */
class AIRewardAnalysisService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiUrl = 'https://api.mistral.ai/v1/chat/completions';
    private RecompenseRepository $recompenseRepository;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        RecompenseRepository $recompenseRepository
    ) {
        $this->apiKey = $params->get('app.mistral_api_key');
        $this->httpClient = $httpClient;
        $this->recompenseRepository = $recompenseRepository;
    }

    /**
     * Analyser une demande de récompense avec l'IA Mistral
     * 
     * @param DemandeRecompense $demand La demande à analyser
     * @return RewardAnalysisDTO Les résultats de l'analyse
     * @throws \Exception En cas d'erreur API
     */
    public function analyzeDemand(DemandeRecompense $demand): RewardAnalysisDTO
    {
        // Construire le contexte pour Mistral
        $prompt = $this->buildAnalysisPrompt($demand);

        try {
            // Appel API Mistral
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
                    'max_tokens' => 1024,
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $content = $response->getContent(false);
                throw new \Exception("Mistral API Error: $content");
            }

            $content = $response->toArray();

            // Parser la réponse Mistral
            if (!isset($content['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid Mistral response format');
            }

            $analysisText = $content['choices'][0]['message']['content'];

            // Parser le texte de réponse en JSON
            return $this->parseAnalysisResponse($analysisText);

        } catch (ClientException $e) {
            throw new \Exception('Mistral API Client Error: ' . $e->getMessage());
        } catch (TransportException $e) {
            throw new \Exception('Mistral API Transport Error: ' . $e->getMessage());
        }
    }

    /**
     * Générer un motif de demande en fonction du choix de récompense
     */
    public function generateMotifSuggestion(DemandeRecompense $demand): string
    {
        $recompenseName = $demand->getRecompense()?->getRecompense() ?? 'Récompense';
        $recompenseType = $demand->getRecompense()?->getType() ?? 'Général';
        $playerName = $demand->getNomDemandeur() ?? 'Le demandeur';
        $existingMotif = trim($demand->getMotif() ?? '');

        // Si le motif existe, améliorer/développer celui-ci
        // Sinon, générer un nouveau motif
        if (!empty($existingMotif)) {
            $prompt = $this->buildImprovedMotifPrompt($playerName, $recompenseName, $recompenseType, $existingMotif);
        } else {
            $prompt = $this->buildNewMotifPrompt($playerName, $recompenseName, $recompenseType);
        }

        return $this->callMistralAPI($prompt, $playerName, $recompenseName, $existingMotif);
    }

    /**
     * Générer un prompt pour créer un NOUVEAU motif (quand champ vide)
     */
    private function buildNewMotifPrompt(string $playerName, string $recompenseName, string $recompenseType): string
    {
        return <<<PROMPT
Tu es un assistant pour une plateforme esports qui aide à rédiger des demandes de récompenses professionnelles.

Rédige un motif de demande court (2-3 phrases) en français à la première personne.

Contexte:
- Nom du demandeur: {$playerName}
- Récompense demandée: {$recompenseName}
- Type de récompense: {$recompenseType}

Contraintes:
- Commence par "Je suis {$playerName} et je ..." ou "Moi, {$playerName}, je ..."
- Ton professionnel, sincère et positif
- 2-3 phrases maximum
- À la première personne du singulier
- Mentionne engagement, performance ou contribution à l'équipe
- Pas de guillemets, aucune liste, aucune notation
PROMPT;
    }

    /**
     * Générer un prompt pour AMÉLIORER/DÉVELOPPER un motif existant
     */
    private function buildImprovedMotifPrompt(string $playerName, string $recompenseName, string $recompenseType, string $existingMotif): string
    {
        return <<<PROMPT
Tu es un assistant pour une plateforme esports qui aide à rédiger des demandes de récompenses professionnelles.

Le demandeur {$playerName} a déjà écrit un motif pour sa demande de {$recompenseName}.

Motif existant:
{$existingMotif}

Tâche: Améliore et développe ce motif en gardant son essence, mais en le rendant plus professionnel, convaincant et détaillé.

Contraintes amélioration:
- Garde la première personne du singulier "Je suis {$playerName}..."
- Conserve les points clés du motif original
- Ajoute plus de détails sur les accomplissements ou l'engagement
- Rend le ton plus professionnel et persuasif
- 3-4 phrases maximum (un peu plus que l'original)
- Aucun guillemet, liste ou notation
- Termine par une phrase affirmative sur la reconnaissance méritée

Retourne UNIQUEMENT le motif amélioré, sans introduction ni explication.
PROMPT;
    }

    /**
     * Appeler l'API Mistral et gérer les erreurs
     */
    private function callMistralAPI(string $prompt, string $playerName, string $recompenseName, string $existingMotif): string
    {
        try {
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
                    'max_tokens' => 300,
                ]
            ]);

            $content = $response->toArray(false);
            $text = $content['choices'][0]['message']['content'] ?? '';
            $text = trim($text);

            if ($text === '') {
                throw new \RuntimeException('Empty Mistral response');
            }

            return $text;
        } catch (\Throwable $e) {
            \error_log('Mistral API error: ' . $e->getMessage());
            
            // Fallback: améliorer le motif existant ou générer un nouveau
            if (!empty($existingMotif)) {
                // Si motif existant, le retourner amélioré légèrement
                return sprintf(
                    "%s %s Cela démontre mon engagement envers %s et mon désir d'excellence. J'estime mériter la reconnaissance '%s' pour mes efforts constants.",
                    $existingMotif,
                    $existingMotif === $existingMotif ? '' : '',
                    $recompenseName,
                    $recompenseName
                );
            } else {
                // Fallback générique pour motif vide
                return sprintf(
                    "Je suis %s et je sollicite l'attribution de %s pour mes efforts et mon engagement constant. J'ai contribué activement aux objectifs de l'équipe et j'estime mériter cette reconnaissance.",
                    $playerName,
                    $recompenseName
                );
            }
        }
    }

    /**
     * Construire le prompt pour l'analyse IA
     * 
     * @param DemandeRecompense $demand
     * @return string Le prompt formaté
     */
    private function buildAnalysisPrompt(DemandeRecompense $demand): string
    {
        $motivations = implode(', ', [
            'Excellence Sportive',
            'Fair-Play',
            'Engagement Communautaire',
            'Innovation',
            'Mentorat',
            'Communication',
            'Leadership'
        ]);

        // Préparer les variables pour éviter les erreurs de syntaxe
        $recompenseName = $demand->getRecompense()?->getRecompense() ?? 'Non spécifiée';
        $recompenseType = $demand->getRecompense()?->getType() ?? 'Non spécifiée';

        return <<<PROMPT
Tu es un expert en validation de demandes de récompenses pour un système de compétitions esports.

Analyse la demande suivante et fournis une évaluation structurée EN FORMAT JSON:

**DEMANDE À ANALYSER:**
Nom du demandeur: {$demand->getNomDemandeur()}
Email: {$demand->getEmail()}
Récompense demandée: {$recompenseName}
Type de récompense: {$recompenseType}

Motif/Justification:
{$demand->getMotif()}

---

**INSTRUCTIONS D'ANALYSE:**

1. **SCORING DE LÉGITIMITÉ (0-100)**
   - 80-100: Très légitime, preuves claires
   - 60-79: Légitime mais certains doutes
   - 40-59: Douteuse, nécessite vérification
   - 0-39: Probablement frauduleuse/spam

2. **TYPE DE FRAUDE** (une seule option):
   - "legitimate": Demande valide et honnête
   - "suspicious": Légitime mais éléments suspects
   - "spam": Tentative manifeste de fraude

3. **POINTS CLÉS** - Extrais 3-5 points importants du texte

4. **SENTIMENT** - Analyse le ton:
   - "positive": Enthousiaste, sincère
   - "neutral": Factuel, sans émotion
   - "negative": Plainte, frustration

5. **SUGGESTION DE RÉCOMPENSE**
   Parmi ces catégories: {$motivations}
   Suggère la catégorie la plus appropriée

6. **CONFIANCE** (0-100): Ton niveau de confiance dans l'analyse

---

**RÉPONDRE EN JSON STRICT (valide):**

\`\`\`json
{
  "legitimacy_score": <nombre 0-100>,
  "fraud_type": "legitimate|suspicious|spam",
  "key_points": [
    "point 1",
    "point 2",
    "point 3"
  ],
  "sentiment": "positive|neutral|negative",
  "suggested_category": "catégorie suggérée",
  "confidence": <nombre 0-100>,
  "analysis_reason": "Explication détaillée de l'analyse (2-3 phrases)",
  "should_auto_approve": <booléen true/false si score >= 80 et legitimate>
}
\`\`\`

**IMPORTANT:** Retourne UNIQUEMENT le JSON, sans texte supplémentaire.
PROMPT;
    }

    /**
     * Parser la réponse JSON de l'API
     * 
     * @param string $responseText
     * @return RewardAnalysisDTO
     */
    private function parseAnalysisResponse(string $responseText): RewardAnalysisDTO
    {
        // Extraire le JSON du texte
        $jsonPattern = '/\{[\s\S]*\}/';
        if (!preg_match($jsonPattern, $responseText, $matches)) {
            throw new \Exception('Could not extract JSON from API response');
        }

        $json = json_decode($matches[0], true);

        if (!is_array($json)) {
            throw new \Exception('Invalid JSON in API response: ' . json_last_error_msg());
        }

        // Créer le DTO avec les données parsées
        $analysis = new RewardAnalysisDTO();
        
        $analysis->setLegitimacyScore((int)($json['legitimacy_score'] ?? 0));
        $analysis->setFraudType($json['fraud_type'] ?? 'legitimate');
        $analysis->setKeyPoints($json['key_points'] ?? []);
        $analysis->setSentiment($json['sentiment'] ?? 'neutral');
        $analysis->setSuggestedRewardType($json['suggested_category'] ?? null);
        $analysis->setConfidenceLevel((float)($json['confidence'] ?? 0));
        $analysis->setAnalysisReason($json['analysis_reason'] ?? 'Analyse complétée');
        $analysis->setShouldAutoApprove((bool)($json['should_auto_approve'] ?? false));

        return $analysis;
    }

    /**
     * Analyser toutes les demandes non analysées
     * 
     * Utile pour l'analyse en batch
     * 
     * @param array $demands
     * @return array Retourne un array avec les demandes et leurs analyses
     */
    public function analyzeDemandsBatch(array $demands): array
    {
        $results = [];

        foreach ($demands as $demand) {
            try {
                $analysis = $this->analyzeDemand($demand);
                $results[$demand->getId()] = [
                    'status' => 'success',
                    'analysis' => $analysis,
                ];
            } catch (\Exception $e) {
                $results[$demand->getId()] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Obtenir un score global de confiabilité
     * 
     * Combine plusieurs facteurs pour un jugement final
     * 
     * @param RewardAnalysisDTO $analysis
     * @return array
     */
    public function getConfidabilityScore(RewardAnalysisDTO $analysis): array
    {
        $score = $analysis->getLegitimacyScore();
        
        // Ajustements basés sur le type de fraude
        if ($analysis->getFraudType() === 'spam') {
            $score = max(0, $score - 40);
        } elseif ($analysis->getFraudType() === 'suspicious') {
            $score = max(0, $score - 20);
        }

        return [
            'final_score' => $score,
            'level' => match(true) {
                $score >= 80 => 'TRUST',
                $score >= 60 => 'ACCEPTABLE',
                $score >= 40 => 'REVIEW_NEEDED',
                default => 'REJECT'
            },
            'recommendation' => $this->getRecommendation($score, $analysis->getFraudType()),
        ];
    }

    /**
     * Obtenir une recommandation basée sur le score
     * 
     * @param int $score
     * @param string $fraudType
     * @return string
     */
    private function getRecommendation(int $score, string $fraudType): string
    {
        if ($fraudType === 'spam') {
            return 'Rejeter - Tentative de fraude détectée';
        }

        if ($score >= 80) {
            return 'Auto-approuver - Forte légitimité';
        } elseif ($score >= 60) {
            return 'Approuver avec vérification mineure';
        } elseif ($score >= 40) {
            return 'Besoin de vérification supplémentaire';
        } else {
            return 'Rejeter - Légitimité insuffisante';
        }
    }
}
