<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne(targetEntity: Reclamation::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Reclamation $reclamation = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private bool $isRead = false;

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getTitle(): string 
    { 
        return $this->title; 
    }

    public function setTitle(string $title): static 
    { 
        $this->title = $title; 
        return $this; 
    }

    public function getMessage(): ?string 
    { 
        return $this->message; 
    }

    public function setMessage(?string $message): static 
    { 
        $this->message = $message; 
        return $this; 
    }

    public function getReclamation(): ?Reclamation 
    { 
        return $this->reclamation; 
    }

    public function setReclamation(?Reclamation $r): static 
    { 
        $this->reclamation = $r; 
        return $this; 
    }

    public function getUser(): ?User 
    { 
        return $this->user; 
    }

    public function setUser(?User $user): static 
    { 
        $this->user = $user; 
        return $this; 
    }

    public function getCreatedAt(): ?\DateTimeImmutable 
    { 
        return $this->createdAt; 
    }

    public function isRead(): bool 
    { 
        return $this->isRead; 
    }

    public function setIsRead(bool $v): static 
    { 
        $this->isRead = $v; 
        return $this; 
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }
}
