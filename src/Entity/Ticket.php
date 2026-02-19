<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'ticket')]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Match is required')]
    private ?Game $game = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Ticket number is required')]
    #[Assert\Length(min: 1, max: 100, minMessage: 'Ticket number cannot be empty', maxMessage: 'Ticket number cannot exceed 100 characters')]
    private ?string $ticketNumber = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Ticket type is required')]
    #[Assert\Choice(choices: ['regular', 'vip', 'student'], message: 'Invalid ticket type')]
    private ?string $type = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Price is required')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Price must be 0 or higher')]
    private ?float $price = 0.0;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Quantity is required')]
    #[Assert\GreaterThan(value: 0, message: 'Quantity must be greater than 0')]
    private ?int $quantity = 0;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Sold must be 0 or higher')]
    private ?int $sold = 0;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['available', 'sold_out', 'cancelled'], message: 'Invalid status')]
    private ?string $status = 'available';

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->status = 'available';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTicketNumber(): ?string
    {
        return $this->ticketNumber;
    }

    public function setTicketNumber(string $ticketNumber): static
    {
        $this->ticketNumber = $ticketNumber;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getSold(): ?int
    {
        return $this->sold;
    }

    public function setSold(int $sold): static
    {
        $this->sold = $sold;
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

    public function getAvailableSeats(): int
    {
        return max(0, $this->quantity - $this->sold);
    }

    public function getOccupancyPercentage(): float
    {
        if ($this->quantity === 0) {
            return 0;
        }
        return ($this->sold / $this->quantity) * 100;
    }

    public function __toString(): string
    {
        return sprintf('[%s] %s - %s', $this->ticketNumber, ucfirst($this->type), $this->price . '€');
    }
}
