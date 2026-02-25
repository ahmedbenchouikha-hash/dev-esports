<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
<<<<<<< HEAD
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
=======
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Tournament name is required')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'The name must be at least {{ limit }} characters', maxMessage: 'The name cannot be longer than {{ limit }} characters')]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Start date is required')]
    #[Assert\LessThan(propertyPath: 'endDate', message: 'Start date must be before end date')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'End date is required')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['pending', 'ongoing', 'completed', 'cancelled'])]
    private ?string $status = 'pending';

<<<<<<< HEAD
    #[ORM\Column(length: 255)]
=======
    #[ORM\Column(length: 255, nullable: true)]
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
    #[Assert\NotBlank(message: 'Location is required')]
    private ?string $location = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Prize pool must be 0 or greater')]
    private ?float $prizePool = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Assert\Type(type: 'array', message: 'Rules must be an array')]
    #[Assert\Count(max: 100, maxMessage: 'You cannot have more than {{ limit }} rules')]
    #[Assert\All(constraints: [
        new Assert\Type(type: 'string', message: 'Each rule must be a string'),
        new Assert\NotBlank(message: 'Rules cannot contain empty values'),
        new Assert\Length(min: 1, max: 500, minMessage: 'Each rule must be at least {{ limit }} character', maxMessage: 'Each rule cannot exceed {{ limit }} characters'),
    ])]
    private ?array $rules = null;

<<<<<<< HEAD
    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'tournament', targetEntity: Game::class, cascade: ['remove'])]
    private Collection $games;

    #[ORM\OneToMany(mappedBy: 'tournament', targetEntity: TournamentRegistration::class, cascade: ['remove'])]
    private Collection $registrations;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->registrations = new ArrayCollection();
=======
    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getPrizePool(): ?float
    {
        return $this->prizePool;
    }

    public function setPrizePool(?float $prizePool): static
    {
        $this->prizePool = $prizePool;
        return $this;
    }

    public function getRules(): ?array
    {
        return $this->rules;
    }

    public function setRules(?array $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function addRule(string $rule): static
    {
        $current = $this->rules ?? [];
        $current[] = $rule;
        $this->rules = array_values($current);
        return $this;
    }

    public function removeRule(string $rule): static
    {
        $current = $this->rules ?? [];
        if (($idx = array_search($rule, $current, true)) !== false) {
            array_splice($current, $idx, 1);
        }
        $this->rules = array_values($current);
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

<<<<<<< HEAD
    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

=======
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

<<<<<<< HEAD
    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setTournament($this);
        }
        return $this;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            if ($game->getTournament() === $this) {
                $game->setTournament(null);
            }
        }
        return $this;
    }

    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(TournamentRegistration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setTournament($this);
        }
        return $this;
    }

    public function removeRegistration(TournamentRegistration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            if ($registration->getTournament() === $this) {
                $registration->setTournament(null);
            }
        }
        return $this;
    }

    public function getApprovedRegistrations(): Collection
    {
        return $this->registrations->filter(fn(TournamentRegistration $reg) => $reg->isApproved());
    }

    public function getPendingRegistrations(): Collection
    {
        return $this->registrations->filter(fn(TournamentRegistration $reg) => $reg->isPending());
    }

    public function getRejectedRegistrations(): Collection
    {
        return $this->registrations->filter(fn(TournamentRegistration $reg) => $reg->isRejected());
    }

=======
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
    public function __toString(): string
    {
        return $this->name ?? 'Tournament';
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 1c04895fd40ddf3e3d0493c052d9fac6b47ed96e
