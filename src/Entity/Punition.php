<?php

namespace App\Entity;

use App\Repository\PunitionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Enum\StatutPunition;

#[ORM\Entity(repositoryClass: PunitionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Punition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de début est requise.')]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de fin est requise.')]
    private ?\DateTimeImmutable $endAt = null;

    // Relation OneToOne avec Reclamation (owning side)
    #[ORM\OneToOne(targetEntity: Reclamation::class, inversedBy: 'punition', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reclamation $reclamation = null;

    #[ORM\Column(length: 20)]
    private string $playerStatus = 'ACTIF';

    // Getters / Setters
    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getStartAt(): ?\DateTimeImmutable 
    { 
        return $this->startAt; 
    }

    public function setStartAt(?\DateTimeImmutable $startAt = null): static
    {
        $this->startAt = $startAt ?? new \DateTimeImmutable();
        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable 
    { 
        return $this->endAt; 
    }

    public function setEndAt(?\DateTimeImmutable $endAt = null): static
    {
        $this->endAt = $endAt ?? ($this->startAt ? $this->startAt->modify('+1 day') : new \DateTimeImmutable('+1 day'));
        return $this;
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->startAt && $this->endAt && $this->endAt <= $this->startAt) {
            $context->buildViolation('La date de fin doit être postérieure à la date de début.')
                ->atPath('endAt')
                ->addViolation();
        }
    }

    public function getReclamation(): ?Reclamation 
    { 
        return $this->reclamation; 
    }

    public function setReclamation(Reclamation $reclamation): static
    {
        $this->reclamation = $reclamation;

        // Assure la cohérence bidirectionnelle
        if ($reclamation->getPunition() !== $this) {
            $reclamation->setPunition($this);
        }

        return $this;
    }

    public function getPlayerStatus(): string 
    { 
        return $this->playerStatus; 
    }

    public function setPlayerStatus(string $playerStatus): static
    {
        $this->playerStatus = $playerStatus;
        return $this;
    }

    // Méthode utilitaire
    public function getPlayerId(): ?int
    {
        return $this->reclamation?->getPlayer()?->getId();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->startAt ??= new \DateTimeImmutable();
        $this->endAt ??= $this->startAt->modify('+1 day');
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        // Optional
    }
}
