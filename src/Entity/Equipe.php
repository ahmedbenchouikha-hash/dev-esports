<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Equipe
{
    #[ORM\Id, ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private $id;

    #[ORM\Column(type:"string", length:255)]
    #[Assert\NotBlank(message: 'Le nom de l\'équipe est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit faire au minimum {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9éèêëàâäæîïôõöœùûüçñ\s\-\.]+$/i',
        message: 'Le nom ne peut contenir que des lettres, chiffres, espaces et tirets'
    )]
    private $nom;

    #[ORM\Column(type:"string", length:255, nullable:true)]
    private $logo;

    #[ORM\Column(type:"datetime")]
    private $dateCreation;

    #[ORM\Column(type:"text", nullable:true)]
    #[Assert\Length(max: 1000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')]
    private $description;

    #[ORM\Column(type:"string", length:50)]
    private $statut = 'en attente';

    #[ORM\Column(type:"json", nullable:true)]
    private $membres = [];

    #[ORM\Column(type:"integer", nullable:true)]
    private $captainId;

    #[ORM\Column(type:"string", length:50, nullable:true)]
    private $couleurEquipe;

    #[ORM\Column(type:"string", length:100, nullable:true)]
    private $jeu;

    #[ORM\Column(type:"string", length:50, nullable:true)]
    private $niveau;

    #[ORM\Column(type:"datetime", nullable:true)]
    private $dateValidation;

    #[ORM\Column(type:"integer", nullable:true)]
    private $score = 0;

    public function __construct() {
        $this->dateCreation = new \DateTime();
    }

    // Getters & Setters...
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getLogo(): ?string { return $this->logo; }
    public function setLogo(?string $logo): self { $this->logo = $logo; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(\DateTimeInterface $dateCreation): self { $this->dateCreation = $dateCreation; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }

    public function getMembres(): ?array { return $this->membres; }
    public function setMembres(?array $membres): self { $this->membres = $membres; return $this; }

    public function getCaptainId(): ?int { return $this->captainId; }
    public function setCaptainId(?int $captainId): self { $this->captainId = $captainId; return $this; }

    public function getCouleurEquipe(): ?string { return $this->couleurEquipe; }
    public function setCouleurEquipe(?string $couleurEquipe): self { $this->couleurEquipe = $couleurEquipe; return $this; }

    public function getJeu(): ?string { return $this->jeu; }
    public function setJeu(?string $jeu): self { $this->jeu = $jeu; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $niveau): self { $this->niveau = $niveau; return $this; }

    public function getDateValidation(): ?\DateTimeInterface { return $this->dateValidation; }
    public function setDateValidation(?\DateTimeInterface $dateValidation): self { $this->dateValidation = $dateValidation; return $this; }

    public function getScore(): ?int { return $this->score; }
    public function setScore(?int $score): self { $this->score = $score; return $this; }
}
