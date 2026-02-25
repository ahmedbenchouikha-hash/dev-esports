<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player extends User
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Player name is required')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $nickname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $playerStatus = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Player must belong to a team')]
    private ?Team $team = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        parent::__construct();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        
        // Ensure player has ROLE_PLAYER
        $roles = $this->getRoles();
        if (!in_array('ROLE_PLAYER', $roles)) {
            $roles[] = 'ROLE_PLAYER';
            $this->setRoles($roles);
        }
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getPlayerStatus(): ?string
    {
        return $this->playerStatus;
    }

    public function setPlayerStatus(?string $playerStatus): static
    {
        $this->playerStatus = $playerStatus;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nickname ?? 'Player';
    }
}