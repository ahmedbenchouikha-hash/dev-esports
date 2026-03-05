<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payment')]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Ticket is required')]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Player $player = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Payment intent ID is required')]
    private ?string $paymentIntentId = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['pending', 'processing', 'succeeded', 'failed', 'refunded'], message: 'Invalid payment status')]
    private ?string $status = 'pending';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Customer email is required')]
    #[Assert\Email(message: 'Invalid email address')]
    private ?string $customerEmail = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull(message: 'Amount is required')]
    #[Assert\GreaterThan(value: 0, message: 'Amount must be greater than 0')]
    private ?string $amount = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Quantity is required')]
    #[Assert\GreaterThan(value: 0, message: 'Quantity must be greater than 0')]
    private ?int $quantityPurchased = 1;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customerName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customerPhone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $qrCode = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $refundedAt = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $refundAmount = null;

    #[ORM\Column]
    private \DateTime $createdAt;

    #[ORM\Column]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->status = 'pending';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;
        return $this;
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

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    public function setPaymentIntentId(string $paymentIntentId): static
    {
        $this->paymentIntentId = $paymentIntentId;
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

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount !== null ? (float) $this->amount : null;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = (string) $amount;
        return $this;
    }

    public function getQuantityPurchased(): ?int
    {
        return $this->quantityPurchased;
    }

    public function setQuantityPurchased(int $quantityPurchased): static
    {
        $this->quantityPurchased = $quantityPurchased;
        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): static
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(?string $customerPhone): static
    {
        $this->customerPhone = $customerPhone;
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

    public function getRefundedAt(): ?\DateTime
    {
        return $this->refundedAt;
    }

    public function setRefundedAt(?\DateTime $refundedAt): static
    {
        $this->refundedAt = $refundedAt;
        return $this;
    }

    public function getRefundAmount(): ?float
    {
        return $this->refundAmount !== null ? (float) $this->refundAmount : null;
    }

    public function setRefundAmount(?float $refundAmount): static
    {
        $this->refundAmount = $refundAmount !== null ? (string) $refundAmount : null;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s - $%s (%s)',
            $this->paymentIntentId,
            $this->customerEmail,
            number_format($this->amount, 2),
            $this->status
        );
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): static
    {
        $this->qrCode = $qrCode;
        return $this;
    }
}
