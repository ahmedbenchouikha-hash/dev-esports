<?php

namespace App\Entity;

use App\Repository\RecompenseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecompenseRepository::class)]
class Recompense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le nom de la récompense est requis')]
    #[Assert\Length(max: 30, maxMessage: 'Le nom ne peut pas dépasser 30 caractères')]
    private ?string $recompense = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type est requis')]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le classement est requis')]
    #[Assert\Range(min: 1, max: 30, notInRangeMessage: 'Le classement doit être entre 1 et 30')]
    private ?int $classement = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'recompense', targetEntity: DemandeRecompense::class)]
    private Collection $demandes;

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecompense(): ?string
    {
        return $this->recompense;
    }

    public function setRecompense(string $recompense): static
    {
        $this->recompense = $recompense;
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

    public function getClassement(): ?int
    {
        return $this->classement;
    }

    public function setClassement(int $classement): static
    {
        $this->classement = $classement;
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

    /**
     * @return Collection<int, DemandeRecompense>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(DemandeRecompense $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setRecompense($this);
        }

        return $this;
    }

    public function removeDemande(DemandeRecompense $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getRecompense() === $this) {
                $demande->setRecompense(null);
            }
        }

        return $this;
    }
}