<?php

namespace App\Entity;

use App\Repository\ChatbotConversationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatbotConversationRepository::class)]
#[ORM\Table(name: 'chatbot_conversations')]
class ChatbotConversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $userMessage = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $aiResponse = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'chatbotConversations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserMessage(): ?string
    {
        return $this->userMessage;
    }

    public function setUserMessage(string $userMessage): static
    {
        $this->userMessage = $userMessage;

        return $this;
    }

    public function getAiResponse(): ?string
    {
        return $this->aiResponse;
    }

    public function setAiResponse(string $aiResponse): static
    {
        $this->aiResponse = $aiResponse;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
