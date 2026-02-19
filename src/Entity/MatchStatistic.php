<?php

namespace App\Entity;

use App\Repository\MatchStatisticRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MatchStatisticRepository::class)]
#[ORM\Table(name: 'match_statistic')]
class MatchStatistic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Player is required')]
    private ?Player $player = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Match is required')]
    private ?Game $game = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Kills is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Kills must be greater than or equal to 0')]
    private ?int $kills = 0;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Deaths is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Deaths must be greater than or equal to 0')]
    private ?int $deaths = 0;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Assists is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Assists must be greater than or equal to 0')]
    private ?int $assists = 0;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Damage dealt is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Damage dealt must be greater than or equal to 0')]
    private ?float $damageDealt = 0.0;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Damage taken is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Damage taken must be greater than or equal to 0')]
    private ?float $damageTaken = 0.0;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Objectives destroyed is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Objectives destroyed must be greater than or equal to 0')]
    private ?int $objectivesDestroyed = 0;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Gold earned is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Gold earned must be greater than or equal to 0')]
    private ?float $goldEarned = 0.0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;
        return $this;
    }

    public function getKills(): ?int
    {
        return $this->kills;
    }

    public function setKills(int $kills): static
    {
        $this->kills = $kills;
        return $this;
    }

    public function getDeaths(): ?int
    {
        return $this->deaths;
    }

    public function setDeaths(int $deaths): static
    {
        $this->deaths = $deaths;
        return $this;
    }

    public function getAssists(): ?int
    {
        return $this->assists;
    }

    public function setAssists(int $assists): static
    {
        $this->assists = $assists;
        return $this;
    }

    public function getDamageDealt(): ?float
    {
        return $this->damageDealt;
    }

    public function setDamageDealt(float $damageDealt): static
    {
        $this->damageDealt = $damageDealt;
        return $this;
    }

    public function getDamageTaken(): ?float
    {
        return $this->damageTaken;
    }

    public function setDamageTaken(float $damageTaken): static
    {
        $this->damageTaken = $damageTaken;
        return $this;
    }

    public function getObjectivesDestroyed(): ?int
    {
        return $this->objectivesDestroyed;
    }

    public function setObjectivesDestroyed(int $objectivesDestroyed): static
    {
        $this->objectivesDestroyed = $objectivesDestroyed;
        return $this;
    }

    public function getGoldEarned(): ?float
    {
        return $this->goldEarned;
    }

    public function setGoldEarned(float $goldEarned): static
    {
        $this->goldEarned = $goldEarned;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
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

    public function getKDA(): float
    {
        if ($this->deaths === 0) {
            return $this->kills + $this->assists;
        }
        return ($this->kills + $this->assists) / $this->deaths;
    }

    public function __toString(): string
    {
        return $this->player?->getNickname() . ' - ' . $this->game?->getId();
    }
}
