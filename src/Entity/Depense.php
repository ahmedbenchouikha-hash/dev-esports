<?php

namespace App\Entity;

use App\Repository\DepenseRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: DepenseRepository::class)]
#[Vich\Uploadable]
class Depense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le titre doit faire au minimum {{ limit }} caractères',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9àâäæéèêëîïôœûüù\s\-\.\(\)]+$/i',
        message: 'Le titre ne peut contenir que des lettres, chiffres et caractères spéciaux (-.,())'
    )]
    private ?string $titre = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le montant est obligatoire')]
    #[Assert\Positive(message: 'Le montant doit être supérieur à 0')]
    #[Assert\Range(
        min: 0.01,
        max: 999999.99,
        notInRangeMessage: 'Le montant doit être entre 0,01 € et 999 999,99 €'
    )]
    private ?float $montant = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column]
    private ?DateTime $date_creation = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'en_attente';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categorie = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Team $team = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facture = null;

    #[Vich\UploadableField(mapping: 'depense_documents', fileNameProperty: 'facture')]
    private ?File $factureFile = null;

    public function __construct()
    {
        $this->date_creation = new DateTime();
        $this->statut = 'en_attente';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): ?DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;
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

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
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

    public function getFacture(): ?string
    {
        return $this->facture;
    }

    public function setFacture(?string $facture): self
    {
        $this->facture = $facture;
        return $this;
    }

    public function getFactureFile(): ?File
    {
        return $this->factureFile;
    }

    public function setFactureFile(?File $factureFile = null): self
    {
        $this->factureFile = $factureFile;
        if (null !== $factureFile) {
            $this->date_creation = new DateTime();
        }
        return $this;
    }
}
