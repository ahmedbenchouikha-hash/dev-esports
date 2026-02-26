<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
<<<<<<< HEAD
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['user' => User::class, 'player' => Player::class])]
=======
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\UserProfile;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'This email is already used.')]
>>>>>>> module-user
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
<<<<<<< HEAD
    protected ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $typeuser = null;

    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    private string $approvalStatus = 'pending'; // pending, approved, rejected
=======
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
>>>>>>> module-user

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationFile = null;

<<<<<<< HEAD
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserProfile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserProfile $profile = null;

=======
    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserProfile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserProfile $profile = null;

    /**
     * Symfony security roles are derived from `typeuser`.
     * No DB column for roles is stored separately.
     * Example mapping: 'ADMIN' -> ['ROLE_ADMIN'], otherwise ['ROLE_USER']
     */

>>>>>>> module-user
    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< HEAD
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

=======
>>>>>>> module-user
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
<<<<<<< HEAD
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
=======

>>>>>>> module-user
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
<<<<<<< HEAD
        return $this;
    }

=======

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
>>>>>>> module-user
    public function getTypeuser(): ?string
    {
        return $this->typeuser;
    }

    public function setTypeuser(string $typeuser): static
    {
        $this->typeuser = $typeuser;
<<<<<<< HEAD
        return $this;
    }

    public function getApprovalStatus(): string
    {
        return $this->approvalStatus;
    }

    public function setApprovalStatus(string $approvalStatus): static
    {
        $this->approvalStatus = $approvalStatus;
        return $this;
    }

    public function isApproved(): bool
    {
        return $this->approvalStatus === 'approved';
    }

    public function isPending(): bool
    {
        return $this->approvalStatus === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->approvalStatus === 'rejected';
    }

    public function getConfirmationFile(): ?string
    {
        return $this->confirmationFile;
    }

    public function setConfirmationFile(?string $confirmationFile): static
    {
        $this->confirmationFile = $confirmationFile;
=======

>>>>>>> module-user
        return $this;
    }

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(?UserProfile $profile): static
    {
        $this->profile = $profile;
<<<<<<< HEAD
        if ($profile !== null && $profile->getUser() !== $this) {
            $profile->setUser($this);
        }
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
}
=======

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
>>>>>>> module-user
