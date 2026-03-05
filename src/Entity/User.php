<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['user' => User::class, 'player' => Player::class])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationFile = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserProfile::class, cascade: ['persist'], fetch: 'EAGER')]
    private ?UserProfile $profile = null;

    /**
     * @var Collection<int, ChatbotConversation>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ChatbotConversation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $chatbotConversations;

    public function __construct()
    {
        $this->chatbotConversations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
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

    public function getTypeuser(): ?string
    {
        return $this->typeuser;
    }

    public function setTypeuser(string $typeuser): static
    {
        $this->typeuser = $typeuser;
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
        return $this;
    }

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(?UserProfile $profile): static
    {
        $this->profile = $profile;
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

    /**
     * @return Collection<int, ChatbotConversation>
     */
    public function getChatbotConversations(): Collection
    {
        return $this->chatbotConversations;
    }

    public function addChatbotConversation(ChatbotConversation $chatbotConversation): static
    {
        if (!$this->chatbotConversations->contains($chatbotConversation)) {
            $this->chatbotConversations->add($chatbotConversation);
            $chatbotConversation->setUser($this);
        }

        return $this;
    }

    public function removeChatbotConversation(ChatbotConversation $chatbotConversation): static
    {
        if ($this->chatbotConversations->removeElement($chatbotConversation)) {
            if ($chatbotConversation->getUser() === $this) {
                $chatbotConversation->setUser(null);
            }
        }

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
}