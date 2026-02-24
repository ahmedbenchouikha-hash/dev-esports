<?php

namespace App\Entity;

use App\Repository\BudgetAlertRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: BudgetAlertRepository::class)]
class BudgetAlert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Budget::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Budget $budget = null;

    #[ORM\Column(length: 50)]
    private string $type; // 'low_budget', 'high_threshold', 'critical'

    #[ORM\Column]
    private ?float $budgetPercentage = null;

    #[ORM\Column]
    private ?float $remainingAmount = null;

    #[ORM\Column]
    private ?DateTime $sentAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sentTo = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $managerNotificationSentAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $adminNotificationSentAt = null;

    public function __construct()
    {
        $this->sentAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getBudgetPercentage(): ?float
    {
        return $this->budgetPercentage;
    }

    public function setBudgetPercentage(?float $budgetPercentage): self
    {
        $this->budgetPercentage = $budgetPercentage;
        return $this;
    }

    public function getRemainingAmount(): ?float
    {
        return $this->remainingAmount;
    }

    public function setRemainingAmount(?float $remainingAmount): self
    {
        $this->remainingAmount = $remainingAmount;
        return $this;
    }

    public function getSentAt(): ?DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(?DateTime $sentAt): self
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getSentTo(): ?string
    {
        return $this->sentTo;
    }

    public function setSentTo(?string $sentTo): self
    {
        $this->sentTo = $sentTo;
        return $this;
    }

    public function getManagerNotificationSentAt(): ?DateTime
    {
        return $this->managerNotificationSentAt;
    }

    public function setManagerNotificationSentAt(?DateTime $managerNotificationSentAt): self
    {
        $this->managerNotificationSentAt = $managerNotificationSentAt;
        return $this;
    }

    public function getAdminNotificationSentAt(): ?DateTime
    {
        return $this->adminNotificationSentAt;
    }

    public function setAdminNotificationSentAt(?DateTime $adminNotificationSentAt): self
    {
        $this->adminNotificationSentAt = $adminNotificationSentAt;
        return $this;
    }
}
