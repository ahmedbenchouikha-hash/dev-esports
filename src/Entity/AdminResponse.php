<?php

namespace App\Entity;

use App\Repository\AdminResponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdminResponseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AdminResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le message est requis.')]
    #[Assert\Length(min: 3, minMessage: 'Le message est trop court ({{ limit }} caractères minimum).')]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // Relation OneToOne owning side
    #[ORM\OneToOne(targetEntity: Reclamation::class, inversedBy: 'adminResponse', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "reclamation_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Reclamation $reclamation = null;

    // Getters / Setters
    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getMessage(): ?string 
    { 
        return $this->message; 
    }

    public function setMessage(string $message): static 
    { 
        $this->message = $message; 
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

    public function getReclamation(): ?Reclamation 
    { 
        return $this->reclamation; 
    }

    public function setReclamation(Reclamation $reclamation): static
    {
        $this->reclamation = $reclamation;
        if ($reclamation->getAdminResponse() !== $this) {
            $reclamation->setAdminResponse($this);
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
