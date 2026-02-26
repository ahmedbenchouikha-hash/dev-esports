<?php

namespace App\Entity;

<<<<<<< HEAD
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
=======
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity]
>>>>>>> module-user
#[ORM\Table(name: 'password_reset_token')]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
<<<<<<< HEAD
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 128, unique: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $expiresAt = null;
=======
    private User $user;

    #[ORM\Column(type: 'string', length: 128, unique: true)]
    private string $token;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;
>>>>>>> module-user

    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< HEAD
    public function getUser(): ?User
=======
    public function getUser(): User
>>>>>>> module-user
    {
        return $this->user;
    }

<<<<<<< HEAD
    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getToken(): ?string
=======
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): string
>>>>>>> module-user
    {
        return $this->token;
    }

<<<<<<< HEAD
    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
=======
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
>>>>>>> module-user
    {
        return $this->createdAt;
    }

<<<<<<< HEAD
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
=======
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
>>>>>>> module-user
    {
        return $this->expiresAt;
    }

<<<<<<< HEAD
    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }
=======
    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
>>>>>>> module-user
}
