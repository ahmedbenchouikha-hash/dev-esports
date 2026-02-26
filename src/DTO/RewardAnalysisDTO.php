<?php

namespace App\DTO;

/**
 * DTO pour les résultats d'analyse IA des demandes de récompenses
 * 
 * Cette classe encapsule les données retournées par l'IA pour l'analyse
 * et permet la validation des résultats avant intégration en base de données
 */
class RewardAnalysisDTO
{
    private int $legitimacyScore;        // Score de légitimité 0-100
    private string $fraudType;           // 'legitimate', 'suspicious', 'spam'
    private float $confidenceLevel;      // Confiance de l'IA 0-1
    private array $keyPoints;            // Points clés extraits
    private string $sentiment;           // 'positive', 'neutral', 'negative'
    private ?string $suggestedRewardType; // Type de récompense suggéré
    private string $analysisReason;      // Explication détaillée
    private bool $shouldAutoApprove;     // Si score > 80 et légitime
    private ?\DateTime $analyzedAt;      // Timestamp de l'analyse

    public function __construct(
        int $legitimacyScore = 0,
        string $fraudType = 'legitimate',
        float $confidenceLevel = 0.0,
        array $keyPoints = [],
        string $sentiment = 'neutral',
        ?string $suggestedRewardType = null,
        string $analysisReason = '',
        bool $shouldAutoApprove = false
    ) {
        $this->legitimacyScore = $legitimacyScore;
        $this->fraudType = $fraudType;
        $this->confidenceLevel = $confidenceLevel;
        $this->keyPoints = $keyPoints;
        $this->sentiment = $sentiment;
        $this->suggestedRewardType = $suggestedRewardType;
        $this->analysisReason = $analysisReason;
        $this->shouldAutoApprove = $shouldAutoApprove;
        $this->analyzedAt = new \DateTime();
    }

    // Getters
    public function getLegitimacyScore(): int
    {
        return $this->legitimacyScore;
    }

    public function getFraudType(): string
    {
        return $this->fraudType;
    }

    public function getConfidenceLevel(): float
    {
        return $this->confidenceLevel;
    }

    public function getKeyPoints(): array
    {
        return $this->keyPoints;
    }

    public function getSentiment(): string
    {
        return $this->sentiment;
    }

    public function getSuggestedRewardType(): ?string
    {
        return $this->suggestedRewardType;
    }

    public function getAnalysisReason(): string
    {
        return $this->analysisReason;
    }

    public function shouldAutoApprove(): bool
    {
        return $this->shouldAutoApprove;
    }

    public function getAnalyzedAt(): ?\DateTime
    {
        return $this->analyzedAt;
    }

    // Setters
    public function setLegitimacyScore(int $score): self
    {
        $this->legitimacyScore = max(0, min(100, $score));
        return $this;
    }

    public function setFraudType(string $type): self
    {
        $this->fraudType = $type;
        return $this;
    }

    public function setConfidenceLevel(float $level): self
    {
        $this->confidenceLevel = max(0.0, min(1.0, $level));
        return $this;
    }

    public function setKeyPoints(array $points): self
    {
        $this->keyPoints = $points;
        return $this;
    }

    public function setSentiment(string $sentiment): self
    {
        $this->sentiment = $sentiment;
        return $this;
    }

    public function setSuggestedRewardType(?string $type): self
    {
        $this->suggestedRewardType = $type;
        return $this;
    }

    public function setAnalysisReason(string $reason): self
    {
        $this->analysisReason = $reason;
        return $this;
    }

    public function setShouldAutoApprove(bool $approve): self
    {
        $this->shouldAutoApprove = $approve;
        return $this;
    }

    /**
     * Convertir le DTO en array pour stockage en base de données
     */
    public function toArray(): array
    {
        return [
            'legitimacy_score' => $this->legitimacyScore,
            'fraud_type' => $this->fraudType,
            'confidence_level' => $this->confidenceLevel,
            'key_points' => json_encode($this->keyPoints),
            'sentiment' => $this->sentiment,
            'suggested_reward_type' => $this->suggestedRewardType,
            'analysis_reason' => $this->analysisReason,
            'should_auto_approve' => $this->shouldAutoApprove,
            'analyzed_at' => $this->analyzedAt,
        ];
    }

    /**
     * Créer un DTO à partir d'un array (hydratation depuis la base de données)
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->legitimacyScore = $data['legitimacy_score'] ?? 0;
        $dto->fraudType = $data['fraud_type'] ?? 'legitimate';
        $dto->confidenceLevel = $data['confidence_level'] ?? 0.0;
        $dto->keyPoints = json_decode($data['key_points'] ?? '[]', true) ?? [];
        $dto->sentiment = $data['sentiment'] ?? 'neutral';
        $dto->suggestedRewardType = $data['suggested_reward_type'] ?? null;
        $dto->analysisReason = $data['analysis_reason'] ?? '';
        $dto->shouldAutoApprove = $data['should_auto_approve'] ?? false;
        $dto->analyzedAt = isset($data['analyzed_at']) ? new \DateTime($data['analyzed_at']) : new \DateTime();
        return $dto;
    }
}
