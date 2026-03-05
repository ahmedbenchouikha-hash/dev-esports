<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use App\Enum\ReclamationType;
use App\Enum\ReclamationStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est requis.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est requise.')]
    #[Assert\Length(min: 10, minMessage: 'La description est trop courte ({{ limit }} caractères minimum).')]
    private ?string $description = null;

    #[ORM\Column(enumType: ReclamationType::class)]
    #[Assert\NotNull(message: 'insérer le type')]
    private ?ReclamationType $type = null;

    #[ORM\Column(enumType: ReclamationStatus::class)]
    #[Assert\NotNull(message: 'L\'état est requis.')]
    private ?ReclamationStatus $etat = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachmentFilename = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Player $player = null;

    // Relation OneToOne inverse side with AdminResponse
    #[ORM\OneToOne(mappedBy: 'reclamation', targetEntity: AdminResponse::class, cascade: ['persist', 'remove'])]
    private ?AdminResponse $adminResponse = null;

    // Relation OneToOne inverse side with Punition
    #[ORM\OneToOne(mappedBy: 'reclamation', targetEntity: Punition::class, cascade: ['persist', 'remove'])]
    private ?Punition $punition = null;

    // Getters / Setters
    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getTitre(): ?string 
    { 
        return $this->titre; 
    }

    public function setTitre(string $titre): static 
    { 
        $this->titre = $titre; 
        return $this; 
    }

    public function getDescription(): ?string 
    { 
        return $this->description; 
    }

    public function setDescription(string $description): static 
    { 
        $this->description = $description; 
        return $this; 
    }

    public function getType(): ?ReclamationType 
    { 
        return $this->type; 
    }

    public function setType(ReclamationType $type): static
    {
        $this->type = $type;
        if ($type !== ReclamationType::JOUEUR) {
            $this->punition = null;
            $this->player = null;
        }
        return $this;
    }

    public function getEtat(): ?ReclamationStatus 
    { 
        return $this->etat; 
    }

    public function setEtat(ReclamationStatus $etat): static 
    { 
        $this->etat = $etat; 
        return $this; 
    }

    public function getCreatedAt(): ?\DateTimeImmutable 
    { 
        return $this->createdAt; 
    }

    public function getUpdatedAt(): ?\DateTimeImmutable 
    { 
        return $this->updatedAt; 
    }

    public function getAttachmentFilename(): ?string 
    { 
        return $this->attachmentFilename; 
    }

    public function setAttachmentFilename(?string $name): static 
    { 
        $this->attachmentFilename = $name; 
        return $this; 
    }

    public function getPlayer(): ?Player 
    { 
        return $this->player; 
    }

    public function setPlayer(?Player $player): static
    {
        if ($this->type === ReclamationType::JOUEUR && $player === null) {
            throw new \InvalidArgumentException('Une réclamation de type JOUEUR doit avoir un joueur associé.');
        }
        $this->player = $player;
        return $this;
    }

    public function getPlayerId(): ?int
    {
        return $this->player?->getId();
    }

    #[Assert\Callback]
    public function validatePlayerId(ExecutionContextInterface $context): void
    {
        if ($this->type === ReclamationType::JOUEUR && $this->player === null) {
            $context->buildViolation('Une réclamation de type JOUEUR doit avoir un joueur associé.')
                ->atPath('player')
                ->addViolation();
        }
    }

    public function getAdminResponse(): ?AdminResponse 
    { 
        return $this->adminResponse; 
    }

    public function setAdminResponse(?AdminResponse $adminResponse): static
    {
        $this->adminResponse = $adminResponse;
        if ($adminResponse && $adminResponse->getReclamation() !== $this) {
            $adminResponse->setReclamation($this);
        }
        return $this;
    }

    public function getPunition(): ?Punition 
    { 
        return $this->punition; 
    }

    public function setPunition(?Punition $punition): static
    {
        $this->punition = $punition;
        if ($punition && $punition->getReclamation() !== $this) {
            $punition->setReclamation($this);
        }
        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
        $this->updatedAt ??= new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
