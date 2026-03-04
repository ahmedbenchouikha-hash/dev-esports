<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[Vich\Uploadable]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The allocated amount is required')]
    #[Assert\Positive(message: 'The amount must be greater than 0')]
    #[Assert\NotNull(message: 'The amount cannot be null')]
    #[Assert\Range(
        min: 0.01,
        max: 9999999.99,
        notInRangeMessage: 'The amount must be between 0.01 € and 9,999,999.99 €'
    )]
    private ?float $montantAlloue = null;

    #[ORM\Column]
    private ?float $montantUtilise = 0;

    #[ORM\Column]
    private ?DateTime $dateAllocation = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'You must select a team')]
    private ?Team $team = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'actif';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificatif = null;

    #[Vich\UploadableField(mapping: 'budget_justificatif', fileNameProperty: 'justificatif')]
    private ?File $justificatifFile = null;

    public function __construct()
    {
        $this->dateAllocation = new DateTime();
        $this->montantUtilise = 0;
        $this->statut = 'actif';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontantAlloue(): ?float
    {
        return $this->montantAlloue;
    }

    public function setMontantAlloue(float $montantAlloue): self
    {
        $this->montantAlloue = $montantAlloue;
        return $this;
    }

    public function getMontantUtilise(): ?float
    {
        return $this->montantUtilise;
    }

    public function setMontantUtilise(float $montantUtilise): self
    {
        $this->montantUtilise = $montantUtilise;
        return $this;
    }

    public function getMontantRestant(): float
    {
        return $this->montantAlloue - $this->montantUtilise;
    }

    public function getPourcentageUtilisation(): float
    {
        if ($this->montantAlloue == 0) {
            return 0;
        }
        return round(($this->montantUtilise / $this->montantAlloue) * 100, 2);
    }

    public function isDepassement(): bool
    {
        return $this->montantUtilise > $this->montantAlloue;
    }

    public function getDateAllocation(): ?DateTime
    {
        return $this->dateAllocation;
    }

    public function setDateAllocation(DateTime $dateAllocation): self
    {
        $this->dateAllocation = $dateAllocation;
        return $this;
    }

    public function getDateModification(): ?DateTime
    {
        return $this->dateModification;
    }

    public function setDateModification(?DateTime $dateModification): self
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getJustificatif(): ?string
    {
        return $this->justificatif;
    }

    public function setJustificatif(?string $justificatif): self
    {
        $this->justificatif = $justificatif;
        return $this;
    }

    public function getJustificatifFile(): ?File
    {
        return $this->justificatifFile;
    }

    public function setJustificatifFile(?File $justificatifFile = null): self
    {
        $this->justificatifFile = $justificatifFile;
        if (null !== $justificatifFile) {
            $this->dateModification = new DateTime();
        }
        return $this;
    }
}
