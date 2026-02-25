<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'gamesAsTeam1')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Team 1 is required')]
    private ?Team $team1 = null;

    #[ORM\ManyToOne(inversedBy: 'gamesAsTeam2')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Team 2 is required')]
    private ?Team $team2 = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Score for team 1 is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Score must be positive')]
    private ?int $score1 = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Score for team 2 is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Score must be positive')]
    private ?int $score2 = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Match date is required')]
    private ?\DateTime $matchdate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Status is required')]
    #[Assert\Choice(choices: ['pending', 'ongoing', 'finished', 'cancelled'], message: 'Invalid status')]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[Assert\NotNull(message: 'Tournament is required')]
    private ?Tournament $tournament = null;

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

    public function getTeam1(): ?Team
    {
        return $this->team1;
    }

    public function setTeam1(?Team $team1): static
    {
        $this->team1 = $team1;
        return $this;
    }

    public function getTeam2(): ?Team
    {
        return $this->team2;
    }

    public function setTeam2(?Team $team2): static
    {
        $this->team2 = $team2;
        return $this;
    }

    public function getScore1(): ?int
    {
        return $this->score1;
    }

    public function setScore1(int $score1): static
    {
        $this->score1 = $score1;

        return $this;
    }

    public function getScore2(): ?int
    {
        return $this->score2;
    }

    public function setScore2(int $score2): static
    {
        $this->score2 = $score2;

        return $this;
    }

    public function getMatchdate(): ?\DateTime
    {
        return $this->matchdate;
    }

    public function setMatchdate(\DateTime $matchdate): static
    {
        $this->matchdate = $matchdate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s vs %s', $this->team1?->getName() ?? 'Team 1', $this->team2?->getName() ?? 'Team 2');
    }
}
