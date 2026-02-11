<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\UserProfile;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'This email is already used.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Please enter a username.')]
    #[Assert\Length(min: 3, max: 50, minMessage: 'Username must be at least {{ limit }} characters.', maxMessage: 'Username cannot be longer than {{ limit }} characters.')]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Please enter an email address.')]
    #[Assert\Email(message: 'The email "{{ value }}" is not a valid email.')]
    #[Assert\Length(max: 180)]
    private ?string $email = null;


    #[ORM\Column(length: 32)]
    #[Assert\NotBlank(message: 'Please select a user type.')]
    #[Assert\Choice(choices: ['ADMIN', 'USER'], message: 'Choose a valid user type.')]
    private ?string $typeuser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationFile = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserProfile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserProfile $profile = null;

    /**
     * Symfony security roles are derived from `typeuser`.
     * No DB column for roles is stored separately.
     * Example mapping: 'ADMIN' -> ['ROLE_ADMIN'], otherwise ['ROLE_USER']
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * A visual identifier that represents this user (required by Symfony >=5.3)
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Return roles based on `typeuser` value.
     */
    public function getRoles(): array
    {
        if ($this->typeuser === 'ADMIN') {
            return ['ROLE_ADMIN'];
        }

        return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
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

    // role removed — use `typeuser` to distinguish 'admin' or 'organisateur'

    /**
     * Type of user: 'admin' or 'organisateur'
     */
    public function getTypeuser(): ?string
    {
        return $this->typeuser;
    }

    public function setTypeuser(string $typeuser): static
    {
        $this->typeuser = $typeuser;

        return $this;
    }

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(?UserProfile $profile): static
    {
        $this->profile = $profile;

        // ensure the owning side is updated
        if ($profile !== null && $profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        return $this;
    }

    public function getConfirmationFile(): ?string
    {
        return $this->confirmationFile;
    }

    public function setConfirmationFile(?string $confirmationFile): static
    {
        $this->confirmationFile = $confirmationFile;

        return $this;
    }
}
