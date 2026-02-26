<?php

namespace App\Service;

use App\Entity\ChatMessage;
use App\Entity\User;
use App\Repository\ChatMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ChatbotService
{
    private array $responses = [
        'greeting' => [
            'Hi there! How can I help you today?',
            'Hello! Welcome to Esports Dev. What can I assist you with?',
            'Hey! What brings you here today?',
        ],
        'help' => [
            'I can help you with various topics including account management, team setup, tournaments, and more. What would you like to know?',
            'I\'m here to assist with your esports development platform. Ask me about teams, tournaments, accounts, or getting started!',
        ],
        'account' => [
            'For account-related issues, you can reset your password, update your profile, or change your settings in your dashboard.',
            'You can manage your account through your profile settings. Would you like help with a specific account feature?',
        ],
        'team' => [
            'Teams are the core of esports competition. You can create a team, invite members, and manage team settings from your dashboard.',
            'Want to create or join a team? I can guide you through the process!',
        ],
        'tournament' => [
            'Tournaments bring teams together for competition. You can browse, create, or join tournaments from the main dashboard.',
            'Interested in tournaments? You can view active tournaments and register your team to compete!',
        ],
        'profile' => [
            'Your profile is where you showcase your esports identity. Update your avatar, bio, and social links to make your profile stand out!',
            'A great profile helps you connect with other players. Customize your profile information anytime.',
        ],
        'features' => [
            'We offer team management, tournament registration, live scoring, player profiles, and matchmaking systems.',
            'Our platform includes: Teams, Tournaments, Player Profiles, Live Scoring, and Community Forums.',
        ],
        'contact' => [
            'You can reach our support team through the contact form in the footer or email us at support@esportsdev.com',
            'Need direct support? Visit our contact page or email us - we\'re here to help!',
        ],
        'default' => [
            'That\'s an interesting question! Could you tell me more about what you\'re looking for?',
            'I\'m not entirely sure about that one. Can you rephrase your question?',
            'Good question! You might also want to check our documentation or contact support for more details.',
        ],
    ];

    public function __construct(
        private readonly ChatMessageRepository $chatRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Process user message and generate bot response
     */
    public function processMessage(string $userMessage, ?User $user = null): ChatMessage
    {
        try {
            // Determine category and generate response
            $category = $this->categorizeMessage($userMessage);
            $botResponse = $this->generateResponse($category);

            // Create and persist chat message
            $chatMessage = new ChatMessage();
            $chatMessage->setUserMessage($userMessage)
                ->setBotResponse($botResponse)
                ->setCategory($category)
                ->setCreatedAt(new \DateTimeImmutable());

            if ($user) {
                $chatMessage->setUser($user);
            }

            $this->entityManager->persist($chatMessage);
            $this->entityManager->flush();

            $this->logger->info("Chat message processed. Category: {$category}");

            return $chatMessage;
        } catch (\Exception $e) {
            $this->logger->error("Error processing chat message: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Categorize user message to determine response type
     */
    private function categorizeMessage(string $message): string
    {
        $message = strtolower(trim($message));

        $categories = [
            'greeting' => ['hello', 'hi', 'hey', 'greetings', 'what\'s up', 'good morning', 'good afternoon', 'good evening'],
            'help' => ['help', 'assist', 'how can', 'what can', 'guide', 'support'],
            'account' => ['account', 'password', 'profile', 'user', 'login', 'reset', 'email'],
            'team' => ['team', 'create team', 'join team', 'members', 'organization'],
            'tournament' => ['tournament', 'competition', 'league', 'match', 'game', 'compete'],
            'profile' => ['profile', 'avatar', 'bio', 'settings', 'personalize'],
            'features' => ['features', 'what do you offer', 'capabilities', 'functions'],
            'contact' => ['contact', 'email', 'support', 'reach out'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'default';
    }

    /**
     * Generate response based on category
     */
    private function generateResponse(string $category): string
    {
        $responses = $this->responses[$category] ?? $this->responses['default'];

        // Return a random response from the category
        return $responses[array_rand($responses)];
    }

    /**
     * Get chat history for a user
     */
    public function getChatHistory(?User $user, int $limit = 20): array
    {
        if (!$user) {
            return [];
        }

        return $this->chatRepository->findRecentByUser($user, $limit);
    }

    /**
     * Get popular topics
     */
    public function getPopularTopics(int $limit = 5): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $topics = $queryBuilder
            ->select('c.category as topic, COUNT(c.id) as count')
            ->from(ChatMessage::class, 'c')
            ->groupBy('c.category')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $topics;
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $totalMessages = (int)$queryBuilder
            ->select('COUNT(c.id)')
            ->from(ChatMessage::class, 'c')
            ->getQuery()
            ->getSingleScalarResult();

        $uniqueUsers = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(DISTINCT c.user)')
            ->from(ChatMessage::class, 'c')
            ->where('c.user IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $messagesByCategory = $this->entityManager->createQueryBuilder()
            ->select('c.category, COUNT(c.id) as count')
            ->from(ChatMessage::class, 'c')
            ->groupBy('c.category')
            ->getQuery()
            ->getResult();

        return [
            'totalMessages' => $totalMessages,
            'uniqueUsers' => $uniqueUsers,
            'messagesByCategory' => $messagesByCategory,
        ];
    }
}
