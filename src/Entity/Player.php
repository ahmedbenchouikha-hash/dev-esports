<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
<<<<<<< HEAD
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
=======
>>>>>>> module-rewards
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player extends User // <--- 1. Extends User
{
    // 2. REMOVED: #[ORM\Id], #[ORM\GeneratedValue], and the $id property.
    // They are now inherited from the User entity.

<<<<<<< HEAD
    #[ORM\Column(length: 255, nullable: true)]
=======
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Player name is required')]
    #[Assert\Length(min: 2, max: 255)]
>>>>>>> module-rewards
    private ?string $nickname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $playerStatus = null;

<<<<<<< HEAD
    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'players')]
    private Collection $teams;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'player', cascade: ['remove'])]
    private Collection $payments;
=======
    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Player must belong to a team')]
    private ?Team $team = null;
>>>>>>> module-rewards

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
<<<<<<< HEAD
        $this->teams = new ArrayCollection();
        $this->payments = new ArrayCollection();
=======
>>>>>>> module-rewards
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // REMOVED: getId() method. It is inherited from User.

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

<<<<<<< HEAD
    public function setNickname(?string $nickname): static
=======
    public function setNickname(string $nickname): static
>>>>>>> module-rewards
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

<<<<<<< HEAD
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->addPlayer($this);
        }
        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            $team->removePlayer($this);
        }
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->teams->first() ?: null;
=======
    public function getTeam(): ?Team
    {
        return $this->team;
>>>>>>> module-rewards
    }

    public function setTeam(?Team $team): static
    {
<<<<<<< HEAD
        // For backward compatibility, clear teams and add the new one
        $this->teams->clear();
        if ($team !== null) {
            $this->addTeam($team);
        }
        return $this;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setPlayer($this);
        }
        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            $payment->setPlayer(null);
        }
=======
        $this->team = $team;
>>>>>>> module-rewards
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