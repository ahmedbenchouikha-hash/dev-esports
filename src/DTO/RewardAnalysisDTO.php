<?php

namespace App\DTO;

class RewardAnalysisDTO
{
    private ?int $legitimacyScore = null;
    private ?string $fraudType = null;
    private ?float $confidenceLevel = null;
    private ?array $keyPoints = null;
    private ?string $sentiment = null;
    private ?string $suggestedRewardType = null;
    private ?string $analysisReason = null;
    private bool $shouldAutoApprove = false;
    private ?\DateTimeInterface $analyzedAt = null;

    public function getLegitimacyScore(): ?int
    {
        return $this->legitimacyScore;
    }

    public function setLegitimacyScore(?int $legitimacyScore): self
    {
        $this->legitimacyScore = $legitimacyScore;
        return $this;
    }

    public function getFraudType(): ?string
    {
        return $this->fraudType;
    }

    public function setFraudType(?string $fraudType): self
    {
        $this->fraudType = $fraudType;
        return $this;
    }

    public function getConfidenceLevel(): ?float
    {
        return $this->confidenceLevel;
    }

    public function setConfidenceLevel(?float $confidenceLevel): self
    {
        $this->confidenceLevel = $confidenceLevel;
        return $this;
    }

    public function getKeyPoints(): ?array
    {
        return $this->keyPoints;
    }

    public function setKeyPoints(?array $keyPoints): self
    {
        $this->keyPoints = $keyPoints;
        return $this;
    }

    public function getSentiment(): ?string
    {
        return $this->sentiment;
    }

    public function setSentiment(?string $sentiment): self
    {
        $this->sentiment = $sentiment;
        return $this;
    }

    public function getSuggestedRewardType(): ?string
    {
        return $this->suggestedRewardType;
    }

    public function setSuggestedRewardType(?string $suggestedRewardType): self
    {
        $this->suggestedRewardType = $suggestedRewardType;
        return $this;
    }

    public function getAnalysisReason(): ?string
    {
        return $this->analysisReason;
    }

    public function setAnalysisReason(?string $analysisReason): self
    {
        $this->analysisReason = $analysisReason;
        return $this;
    }

    public function shouldAutoApprove(): bool
    {
        return $this->shouldAutoApprove;
    }

    public function setShouldAutoApprove(bool $shouldAutoApprove): self
    {
        $this->shouldAutoApprove = $shouldAutoApprove;
        return $this;
    }

    public function getAnalyzedAt(): ?\DateTimeInterface
    {
        return $this->analyzedAt;
    }

    public function setAnalyzedAt(?\DateTimeInterface $analyzedAt): self
    {
        $this->analyzedAt = $analyzedAt;
        return $this;
    }
}
