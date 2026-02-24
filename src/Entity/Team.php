<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Team name is required')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Team name must be at least 2 characters', maxMessage: 'Team name must not exceed 255 characters')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Country is required')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Country must be at least 2 characters', maxMessage: 'Country must not exceed 255 characters')]
    private ?string $country = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: 'Description is required')]
    #[Assert\Length(min: 10, max: 1000, minMessage: 'Description must be at least 10 characters', maxMessage: 'Description must not exceed 1000 characters')]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'Detailed description must not exceed 2000 characters')]
    private ?string $detailedDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|gif)$/i',
        message: 'Invalid logo filename. Only jpg, jpeg, png, gif are allowed'
    )]
    private ?string $logo = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Choice(
        choices: ['LoL', 'CS:GO', 'Dota 2', 'FIFA'],
        message: 'Invalid game choice'
    )]
    private ?string $jeu = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['Débutant', 'Intermédiaire', 'Pro'],
        message: 'Invalid level choice'
    )]
    private ?string $niveau = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Regex(
        pattern: '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i',
        message: 'Invalid color format. Use hex format like #FF0000'
    )]
    private ?string $couleurEquipe = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
        choices: ['en attente', 'approuvé', 'refusé'],
        message: 'Invalid status'
    )]
    private ?string $statut = 'en attente';

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateValidation = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Score must be zero or positive')]
    #[Assert\LessThanOrEqual(value: 1000000, message: 'Score is too high')]
    private ?int $score = 0;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'team1', targetEntity: Game::class)]
    private Collection $gamesAsTeam1;

    #[ORM\OneToMany(mappedBy: 'team2', targetEntity: Game::class)]
    private Collection $gamesAsTeam2;

    #[ORM\ManyToMany(targetEntity: Player::class, inversedBy: 'teams')]
    #[ORM\JoinTable(name: 'player_team')]
    private Collection $players;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->gamesAsTeam1 = new ArrayCollection();
        $this->gamesAsTeam2 = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getJeu(): ?string
    {
        return $this->jeu;
    }

    public function setJeu(?string $jeu): static
    {
        $this->jeu = $jeu;
        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(?string $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getCouleurEquipe(): ?string
    {
        return $this->couleurEquipe;
    }

    public function setCouleurEquipe(?string $couleurEquipe): static
    {
        $this->couleurEquipe = $couleurEquipe;
        return $this;
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

    public function getDateValidation(): ?\DateTime
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTime $dateValidation): static
    {
        $this->dateValidation = $dateValidation;
        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function getDetailedDescription(): ?string
    {
        return $this->detailedDescription;
    }

    public function setDetailedDescription(?string $detailedDescription): static
    {
        $this->detailedDescription = $detailedDescription;
        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            if (!$player->getTeams()->contains($this)) {
                $player->addTeam($this);
            }
        }
        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            $player->removeTeam($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGamesAsTeam1(): Collection
    {
        return $this->gamesAsTeam1;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGamesAsTeam2(): Collection
    {
        return $this->gamesAsTeam2;
    }

    public function __toString(): string
    {
        return $this->name ?? 'Team';
    }
}
