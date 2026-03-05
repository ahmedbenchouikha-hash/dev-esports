<?php

namespace App\Entity;

use App\Repository\DemandeRecompenseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeRecompenseRepository::class)]
class DemandeRecompense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Reward is required')]
    private ?Recompense $recompense = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Requester name is required')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Name must be at least 2 characters',
        maxMessage: 'Name cannot exceed 255 characters'
    )]
    private ?string $nomDemandeur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(
        message: 'Email must have a valid format (include @ and a domain)',
        mode: 'html5'
    )]
    #[Assert\Regex(
        pattern: '/^[^@]+@[^@]+\.(com|tn|fr|org|net|co|ca|de|it|es|uk|be|ch|nl|au|us|jp|cn|in|br|mx|kr|tr)$/i',
        message: 'Email domain must be valid (.com, .tn, .fr, .org, .net, etc.)'
    )]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $createdByEmail = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: 'Request reason is required')]
    #[Assert\Length(
        min: 50,
        max: 1000,
        minMessage: 'Reason must be at least 50 characters',
        maxMessage: 'Reason cannot exceed 1000 characters'
    )]
    private ?string $motif = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'en_attente';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $emailVerifiedAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPrioritaire = false;

    // === CHAMPS IA ===
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $aiLegitimacyScore = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $aiFraudType = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $aiConfidenceLevel = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $aiKeyPoints = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $aiSentiment = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $aiSuggestedRewardType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $aiAnalysisReason = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $aiShouldAutoApprove = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $aiAnalyzedAt = null;

    public function __construct()
    {
        $this->dateDemande = new \DateTime();
        $this->verificationToken = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecompense(): ?Recompense
    {
        return $this->recompense;
    }

    public function setRecompense(?Recompense $recompense): static
    {
        $this->recompense = $recompense;
        return $this;
    }

    public function getNomDemandeur(): ?string
    {
        return $this->nomDemandeur;
    }

    public function setNomDemandeur(string $nomDemandeur): static
    {
        $this->nomDemandeur = $nomDemandeur;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getCreatedByEmail(): ?string
    {
        return $this->createdByEmail;
    }

    public function setCreatedByEmail(?string $createdByEmail): static
    {
        $this->createdByEmail = $createdByEmail;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;
        return $this;
    }

    public function getEmailVerifiedAt(): ?\DateTimeInterface
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTimeInterface $emailVerifiedAt): static
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function getIsPrioritaire(): bool
    {
        return $this->isPrioritaire;
    }

    public function setIsPrioritaire(bool $isPrioritaire): static
    {
        $this->isPrioritaire = $isPrioritaire;
        return $this;
    }

    // === GETTERS/SETTERS IA ===

    public function getAiLegitimacyScore(): ?int
    {
        return $this->aiLegitimacyScore;
    }

    public function setAiLegitimacyScore(?int $score): static
    {
        $this->aiLegitimacyScore = $score;
        return $this;
    }

    public function getAiFraudType(): ?string
    {
        return $this->aiFraudType;
    }

    public function setAiFraudType(?string $type): static
    {
        $this->aiFraudType = $type;
        return $this;
    }

    public function getAiConfidenceLevel(): ?float
    {
        return $this->aiConfidenceLevel;
    }

    public function setAiConfidenceLevel(?float $level): static
    {
        $this->aiConfidenceLevel = $level;
        return $this;
    }

    public function getAiKeyPoints(): ?array
    {
        return $this->aiKeyPoints;
    }

    public function setAiKeyPoints(?array $points): static
    {
        $this->aiKeyPoints = $points;
        return $this;
    }

    public function getAiSentiment(): ?string
    {
        return $this->aiSentiment;
    }

    public function setAiSentiment(?string $sentiment): static
    {
        $this->aiSentiment = $sentiment;
        return $this;
    }

    public function getAiSuggestedRewardType(): ?string
    {
        return $this->aiSuggestedRewardType;
    }

    public function setAiSuggestedRewardType(?string $type): static
    {
        $this->aiSuggestedRewardType = $type;
        return $this;
    }

    public function getAiAnalysisReason(): ?string
    {
        return $this->aiAnalysisReason;
    }

    public function setAiAnalysisReason(?string $reason): static
    {
        $this->aiAnalysisReason = $reason;
        return $this;
    }

    public function getAiShouldAutoApprove(): ?bool
    {
        return $this->aiShouldAutoApprove;
    }

    public function setAiShouldAutoApprove(?bool $approve): static
    {
        $this->aiShouldAutoApprove = $approve;
        return $this;
    }

    public function getAiAnalyzedAt(): ?\DateTimeInterface
    {
        return $this->aiAnalyzedAt;
    }

    public function setAiAnalyzedAt(?\DateTimeInterface $date): static
    {
        $this->aiAnalyzedAt = $date;
        return $this;
    }

    /**
     * Hydrater l'entité à partir d'un RewardAnalysisDTO
     * 
     * @param \App\DTO\RewardAnalysisDTO $analysis
     * @return static
     */
    public function applyAIAnalysis(\App\DTO\RewardAnalysisDTO $analysis): static
    {
        $this->setAiLegitimacyScore($analysis->getLegitimacyScore());
        $this->setAiFraudType($analysis->getFraudType());
        $this->setAiConfidenceLevel($analysis->getConfidenceLevel());
        $this->setAiKeyPoints($analysis->getKeyPoints());
        $this->setAiSentiment($analysis->getSentiment());
        $this->setAiSuggestedRewardType($analysis->getSuggestedRewardType());
        $this->setAiAnalysisReason($analysis->getAnalysisReason());
        $this->setAiShouldAutoApprove($analysis->shouldAutoApprove());
        $this->setAiAnalyzedAt($analysis->getAnalyzedAt());

        // Auto-approuver si recommandé par l'IA
        if ($analysis->shouldAutoApprove() && $analysis->getFraudType() === 'legitimate') {
            $this->setStatut('approuvée');
            $this->setIsPrioritaire(true);
        }

        return $this;
    }

}
