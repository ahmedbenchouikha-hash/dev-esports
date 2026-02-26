<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly ChatbotService $chatbotService)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        $chatHistory = [];
        $statistics = $this->chatbotService->getStatistics();

        if ($user) {
            $chatHistory = $this->chatbotService->getChatHistory($user, 5);
        }

        return $this->render('home/index.html.twig', [
            'chatHistory' => $chatHistory,
            'statistics' => $statistics,
        ]);
    }

    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $userMessage = $data['message'] ?? '';

            if (empty($userMessage)) {
                return new JsonResponse(['error' => 'Message cannot be empty'], 400);
            }

            $user = $this->getUser();
            $chatMessage = $this->chatbotService->processMessage($userMessage, $user);

            return new JsonResponse([
                'success' => true,
                'userMessage' => $chatMessage->getUserMessage(),
                'botResponse' => $chatMessage->getBotResponse(),
                'category' => $chatMessage->getCategory(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred processing your message'], 500);
        }
    }

    #[Route('/api/chat-history', name: 'api_chat_history', methods: ['GET'])]
    public function getChatHistory(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $history = $this->chatbotService->getChatHistory($user, 20);

        return new JsonResponse([
            'success' => true,
            'messages' => array_map(fn($msg) => [
                'userMessage' => $msg->getUserMessage(),
                'botResponse' => $msg->getBotResponse(),
                'category' => $msg->getCategory(),
                'createdAt' => $msg->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $history),
        ]);
    }
}
