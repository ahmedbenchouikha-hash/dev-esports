<?php

namespace App\Entity;

use App\Repository\DemandeRecompenseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeRecompenseRepository::class)]
class DemandeRecompense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La récompense est requise')]
    private ?Recompense $recompense = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du demandeur est requis')]
    private ?string $nomDemandeur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est requis')]
    #[Assert\Email(message: 'Email invalide')]
    private ?string $email = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'en_attente';

    public function __construct()
    {
        $this->dateDemande = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecompense(): ?Recompense
    {
        return $this->recompense;
    }

    public function setRecompense(?Recompense $recompense): static
    {
        $this->recompense = $recompense;
        return $this;
    }

    public function getNomDemandeur(): ?string
    {
        return $this->nomDemandeur;
    }

    public function setNomDemandeur(string $nomDemandeur): static
    {
        $this->nomDemandeur = $nomDemandeur;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
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
}